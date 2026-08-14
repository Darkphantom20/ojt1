"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const express_1 = require("express");
const db_1 = require("../db");
const bcryptjs_1 = __importDefault(require("bcryptjs"));
const jsonwebtoken_1 = __importDefault(require("jsonwebtoken"));
const router = (0, express_1.Router)();
const JWT_SECRET = process.env.JWT_SECRET || 'change_this_secret';
router.post('/login', async (req, res) => {
    const { accessCode, password } = req.body;
    if (!accessCode || !password) {
        return res.status(400).json({ message: 'Access code and password are required.' });
    }
    try {
        const result = await db_1.pool.query('SELECT id, full_name, email, department, password_hash, access_code, status, avatar FROM coordinator_accounts WHERE access_code = $1 LIMIT 1', [accessCode]);
        const rows = result.rows;
        if (!Array.isArray(rows) || rows.length === 0) {
            return res.status(401).json({ message: 'Invalid credentials.' });
        }
        const coordinator = rows[0];
        if (coordinator.status === 'disabled') {
            return res.status(403).json({ message: 'Coordinator account is disabled.' });
        }
        const isValid = await bcryptjs_1.default.compare(password, coordinator.password_hash);
        if (!isValid) {
            return res.status(401).json({ message: 'Invalid credentials.' });
        }
        const token = jsonwebtoken_1.default.sign({
            id: coordinator.id,
            fullName: coordinator.full_name,
            email: coordinator.email,
            department: coordinator.department,
            role: 'coordinator',
        }, JWT_SECRET, { expiresIn: '7d' });
        return res.json({
            token,
            profile: {
                id: coordinator.id,
                fullName: coordinator.full_name,
                email: coordinator.email,
                department: coordinator.department,
                avatar: coordinator.avatar,
            },
        });
    }
    catch (error) {
        console.error(error);
        return res.status(500).json({ message: 'Unable to login.' });
    }
});
router.get('/profile', (req, res) => {
    const authHeader = req.headers.authorization;
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
        return res.status(401).json({ message: 'Authorization required.' });
    }
    const token = authHeader.split(' ')[1];
    try {
        const payload = jsonwebtoken_1.default.verify(token, JWT_SECRET);
        if (payload.role !== 'coordinator') {
            return res.status(403).json({ message: 'Not authorized.' });
        }
        return res.json({
            id: payload.id,
            fullName: payload.fullName,
            email: payload.email,
            department: payload.department,
        });
    }
    catch (error) {
        return res.status(401).json({ message: 'Invalid token.' });
    }
});
exports.default = router;

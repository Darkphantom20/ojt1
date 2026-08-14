"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const express_1 = require("express");
const db_1 = require("../db");
const bcryptjs_1 = __importDefault(require("bcryptjs"));
const router = (0, express_1.Router)();
router.post('/register', async (req, res) => {
    const { studentId, password, name, email, department } = req.body;
    if (!studentId || !password || !name || !email || !department) {
        return res.status(400).json({ message: 'All fields are required.' });
    }
    try {
        const existingResult = await db_1.pool.query('SELECT id FROM students WHERE student_id = $1 OR email = $2 LIMIT 1', [studentId, email]);
        if (existingResult.rows.length > 0) {
            return res.status(409).json({ message: 'Student ID or email already registered.' });
        }
        const passwordHash = await bcryptjs_1.default.hash(password, 10);
        await db_1.pool.query('INSERT INTO students (student_id, password_hash, name, email, department, required_ojt_hours, avatar, registration_status, enrolled_at) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, NOW())', [studentId, passwordHash, name, email, department, 480, '', 'pending_verification']);
        return res.status(201).json({ message: 'Registration successful. Please verify your email.' });
    }
    catch (error) {
        console.error(error);
        return res.status(500).json({ message: 'Unable to register student.' });
    }
});
exports.default = router;

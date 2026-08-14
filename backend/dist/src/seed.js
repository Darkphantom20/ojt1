"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.ensureTestStudent = ensureTestStudent;
const bcryptjs_1 = __importDefault(require("bcryptjs"));
const db_1 = require("./db");
const init_db_1 = require("./init-db");
const TEST_STUDENT_ID = 'S123';
const TEST_PASSWORD = 'password123';
async function ensureTestStudent() {
    try {
        // First, ensure database tables exist
        await (0, init_db_1.initializeDatabase)();
        const result = await db_1.pool.query('SELECT id FROM students WHERE student_id = $1 LIMIT 1', [TEST_STUDENT_ID]);
        if (result.rows.length > 0) {
            console.log(`Test student ${TEST_STUDENT_ID} already exists`);
            return;
        }
        const passwordHash = await bcryptjs_1.default.hash(TEST_PASSWORD, 10);
        await db_1.pool.query('INSERT INTO students (student_id, password_hash, name, email, department, required_ojt_hours, avatar, registration_status, enrolled_at) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, NOW())', [
            TEST_STUDENT_ID,
            passwordHash,
            'Test Student',
            'test@student.local',
            'Computer Science',
            480,
            '',
            'approved',
        ]);
        console.log(`✅ Inserted test student ${TEST_STUDENT_ID} / ${TEST_PASSWORD}`);
    }
    catch (error) {
        console.error('Error in ensureTestStudent:', error);
        throw error;
    }
}

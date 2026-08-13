import bcrypt from 'bcryptjs';
import { pool } from './db';
import { initializeDatabase } from './init-db';

const TEST_STUDENT_ID = 'S123';
const TEST_PASSWORD = 'password123';

export async function ensureTestStudent() {
  try {
    // First, ensure database tables exist
    await initializeDatabase();

    const result = await pool.query<any>(
      'SELECT id FROM students WHERE student_id = $1 LIMIT 1',
      [TEST_STUDENT_ID],
    );

    if (result.rows.length > 0) {
      console.log(`Test student ${TEST_STUDENT_ID} already exists`);
      return;
    }

    const passwordHash = await bcrypt.hash(TEST_PASSWORD, 10);
    await pool.query(
      'INSERT INTO students (student_id, password_hash, name, email, department, required_ojt_hours, avatar, registration_status, enrolled_at) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, NOW())',
      [
        TEST_STUDENT_ID,
        passwordHash,
        'Test Student',
        'test@student.local',
        'Computer Science',
        480,
        '',
        'approved',
      ],
    );

    console.log(`✅ Inserted test student ${TEST_STUDENT_ID} / ${TEST_PASSWORD}`);
  } catch (error) {
    console.error('Error in ensureTestStudent:', error);
    throw error;
  }
}

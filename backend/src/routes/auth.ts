import { Router } from 'express';
import { pool } from '../db';
import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';

const router = Router();

const JWT_SECRET = process.env.JWT_SECRET || 'change_this_secret';

router.post('/login', async (req, res) => {
  const { studentId, password } = req.body;
  if (!studentId || !password) {
    return res.status(400).json({ message: 'Student ID and password are required.' });
  }

  try {
    const result = await pool.query<any>(
      'SELECT id, student_id, password_hash, name, email, department, required_ojt_hours, avatar, registration_status FROM students WHERE student_id = $1 LIMIT 1',
      [studentId],
    );

    const rows = result.rows;
    if (!Array.isArray(rows) || rows.length === 0) {
      return res.status(401).json({ message: 'Invalid credentials.' });
    }

    const student = rows[0] as any;
    if (student.registration_status !== 'approved') {
      return res.status(403).json({ message: 'Account is not approved yet.' });
    }

    const isValid = await bcrypt.compare(password, student.password_hash);
    if (!isValid) {
      return res.status(401).json({ message: 'Invalid credentials.' });
    }

    const token = jwt.sign(
      {
        id: student.id,
        studentId: student.student_id,
        name: student.name,
        email: student.email,
        department: student.department,
      },
      JWT_SECRET,
      { expiresIn: '7d' },
    );

    return res.json({
      token,
      profile: {
        id: student.id,
        studentId: student.student_id,
        name: student.name,
        email: student.email,
        department: student.department,
        requiredOjtHours: student.required_ojt_hours,
        avatar: student.avatar,
      },
    });
  } catch (error) {
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
    const payload = jwt.verify(token, JWT_SECRET) as Record<string, unknown>;
    return res.json({
      id: payload.id,
      studentId: payload.studentId,
      name: payload.name,
      email: payload.email,
      department: payload.department,
    });
  } catch (error) {
    return res.status(401).json({ message: 'Invalid token.' });
  }
});

export default router;

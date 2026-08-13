import { Router } from 'express';
import { pool } from '../db';
import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';

const router = Router();
const JWT_SECRET = process.env.JWT_SECRET || 'change_this_secret';

router.post('/login', async (req, res) => {
  const { username, password } = req.body;
  if (!username || !password) {
    return res.status(400).json({ message: 'Username and password are required.' });
  }

  try {
    const result = await pool.query<any>(
      'SELECT id, username, password_hash, name, email, avatar FROM admin_users WHERE username = $1 LIMIT 1',
      [username],
    );

    const rows = result.rows;
    if (!Array.isArray(rows) || rows.length === 0) {
      return res.status(401).json({ message: 'Invalid credentials.' });
    }

    const admin = rows[0] as any;
    const isValid = await bcrypt.compare(password, admin.password_hash);
    if (!isValid) {
      return res.status(401).json({ message: 'Invalid credentials.' });
    }

    const token = jwt.sign(
      { id: admin.id, username: admin.username, name: admin.name, email: admin.email, role: 'admin' },
      JWT_SECRET,
      { expiresIn: '7d' },
    );

    return res.json({
      token,
      profile: {
        id: admin.id,
        username: admin.username,
        name: admin.name,
        email: admin.email,
        avatar: admin.avatar,
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
    if (payload.role !== 'admin') {
      return res.status(403).json({ message: 'Not authorized.' });
    }

    return res.json({
      id: payload.id,
      username: payload.username,
      name: payload.name,
      email: payload.email,
    });
  } catch (error) {
    return res.status(401).json({ message: 'Invalid token.' });
  }
});

router.get('/dashboard', async (req, res) => {
  const authHeader = req.headers.authorization;
  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    return res.status(401).json({ message: 'Authorization required.' });
  }

  const token = authHeader.split(' ')[1];
  try {
    const payload = jwt.verify(token, JWT_SECRET) as Record<string, unknown>;
    if (payload.role !== 'admin') {
      return res.status(403).json({ message: 'Not authorized.' });
    }

    const studentCountResult = await pool.query<any>('SELECT COUNT(*) AS count FROM students');
    const coordinatorCountResult = await pool.query<any>('SELECT COUNT(*) AS count FROM coordinator_accounts');
    const pendingVerificationResult = await pool.query<any>(
      'SELECT COUNT(*) AS count FROM students WHERE registration_status = $1',
      ['pending_verification'],
    );

    const studentCountRow = studentCountResult.rows[0] || { count: 0 };
    const coordinatorCountRow = coordinatorCountResult.rows[0] || { count: 0 };
    const pendingVerificationRow = pendingVerificationResult.rows[0] || { count: 0 };

    return res.json({
      totalStudents: Number(studentCountRow.count) || 0,
      totalCoordinators: Number(coordinatorCountRow.count) || 0,
      pendingVerifications: Number(pendingVerificationRow.count) || 0,
    });
  } catch (error) {
    console.error(error);
    return res.status(401).json({ message: 'Invalid token.' });
  }
});

export default router;

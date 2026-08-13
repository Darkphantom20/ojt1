import { Router } from 'express';
import { pool } from '../db';
import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';

const router = Router();
const JWT_SECRET = process.env.JWT_SECRET || 'change_this_secret';

router.post('/login', async (req, res) => {
  const { accessCode, password } = req.body;
  if (!accessCode || !password) {
    return res.status(400).json({ message: 'Access code and password are required.' });
  }

  try {
    const result = await pool.query<any>(
      'SELECT id, full_name, email, department, password_hash, access_code, status, avatar FROM coordinator_accounts WHERE access_code = $1 LIMIT 1',
      [accessCode],
    );

    const rows = result.rows;
    if (!Array.isArray(rows) || rows.length === 0) {
      return res.status(401).json({ message: 'Invalid credentials.' });
    }

    const coordinator = rows[0] as any;
    if (coordinator.status === 'disabled') {
      return res.status(403).json({ message: 'Coordinator account is disabled.' });
    }

    const isValid = await bcrypt.compare(password, coordinator.password_hash);
    if (!isValid) {
      return res.status(401).json({ message: 'Invalid credentials.' });
    }

    const token = jwt.sign(
      {
        id: coordinator.id,
        fullName: coordinator.full_name,
        email: coordinator.email,
        department: coordinator.department,
        role: 'coordinator',
      },
      JWT_SECRET,
      { expiresIn: '7d' },
    );

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
    if (payload.role !== 'coordinator') {
      return res.status(403).json({ message: 'Not authorized.' });
    }

    return res.json({
      id: payload.id,
      fullName: payload.fullName,
      email: payload.email,
      department: payload.department,
    });
  } catch (error) {
    return res.status(401).json({ message: 'Invalid token.' });
  }
});

export default router;

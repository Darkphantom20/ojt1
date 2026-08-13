import type { VercelRequest, VercelResponse } from '@vercel/node';
import express from 'express';
import cors from 'cors';
import dotenv from 'dotenv';
import authRoutes from '../src/routes/auth';
import studentRoutes from '../src/routes/student';
import adminRoutes from '../src/routes/admin';
import coordinatorRoutes from '../src/routes/coordinator';
import { ensureTestStudent } from '../src/seed';

dotenv.config();

const app = express();

const frontendUrls = [
  process.env.FRONTEND_URL || 'http://localhost:3000',
  'http://127.0.0.1:3000',
  'http://localhost:3001',
  'https://ojt1.vercel.app',
];

app.use(
  cors({
    origin: (origin, callback) => {
      if (!origin || frontendUrls.includes(origin)) {
        return callback(null, true);
      }
      return callback(new Error('Not allowed by CORS'));
    },
    credentials: true,
  }),
);

app.use(express.json());

app.use('/auth', authRoutes);
app.use('/students', studentRoutes);
app.use('/admin', adminRoutes);
app.use('/coordinator', coordinatorRoutes);

app.get('/health', (_req: express.Request, res: express.Response) => {
  res.json({ status: 'ok' });
});

// Initialize on first request
let initialized = false;
app.use(async (_req: express.Request, _res: express.Response, next: express.NextFunction) => {
  if (!initialized) {
    try {
      await ensureTestStudent();
      initialized = true;
    } catch (error) {
      console.error('Initialization error:', error);
    }
  }
  next();
});

export default app;


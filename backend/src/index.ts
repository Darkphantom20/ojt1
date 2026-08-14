import express, { Request, Response } from 'express';
import cors from 'cors';
import dotenv from 'dotenv';
import path from 'path';
import authRoutes from './routes/auth';
import studentRoutes from './routes/student';
import adminRoutes from './routes/admin';
import coordinatorRoutes from './routes/coordinator';
import { ensureTestStudent } from './seed';

dotenv.config({ path: path.resolve(__dirname, '../.env') });

const app = express();
const port = process.env.PORT || 5000;

const frontendUrls = [
  'http://localhost:3000',
  'http://localhost:3001',
  ...(process.env.FRONTEND_URL ? [process.env.FRONTEND_URL] : []),
  ...((process.env.FRONTEND_URLS || '')
    .split(',')
    .map((url) => url.trim())
    .filter(Boolean)),
  'https://ojt1.vercel.app',
  'https://ojt1monitoringsystem.vercel.app',
];

app.use(
  cors({
    origin: (origin: string | undefined, callback: Function) => {
      if (!origin || frontendUrls.includes(origin)) {
        callback(null, true);
      } else {
        callback(new Error('Not allowed by CORS'));
      }
    },
    credentials: true,
  })
);

app.use(express.json());

app.use('/api/auth', authRoutes);
app.use('/api/students', studentRoutes);
app.use('/api/admin', adminRoutes);
app.use('/api/coordinator', coordinatorRoutes);

app.get('/', (_req: Request, res: Response) => {
  res.json({ status: 'ok', service: 'ojt-backend' });
});

app.get('/api/health', (_req: Request, res: Response) => {
  res.json({ status: 'ok' });
});

export default app;

// Only start server in development mode (when not deployed to Vercel)
if (process.env.NODE_ENV !== 'production' && !process.env.VERCEL) {
  (async () => {
    try {
      await ensureTestStudent();
      app.listen(port, () => {
        console.log(`Backend running on http://localhost:${port}`);
      });
    } catch (error) {
      console.error('Failed to start server:', error);
      process.exit(1);
    }
  })();
}

import express from 'express';
import cors from 'cors';
import dotenv from 'dotenv';
import authRoutes from './routes/auth';
import studentRoutes from './routes/student';
import adminRoutes from './routes/admin';
import coordinatorRoutes from './routes/coordinator';
import { ensureTestStudent } from './seed';

dotenv.config();

const app = express();
const port = process.env.PORT || 5000;

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

app.use('/api/auth', authRoutes);
app.use('/api/students', studentRoutes);
app.use('/api/admin', adminRoutes);
app.use('/api/coordinator', coordinatorRoutes);

app.get('/api/health', (_req, res) => {
  res.json({ status: 'ok' });
});

// For Vercel serverless
export default app;

// For local development
if (process.env.NODE_ENV !== 'production') {
  async function startServer() {
    await ensureTestStudent();
    app.listen(port, () => {
      console.log(`Backend running on http://localhost:${port}`);
    });
  }

  startServer().catch((error) => {
    console.error('Server failed to start:', error);
    process.exit(1);
  });
}

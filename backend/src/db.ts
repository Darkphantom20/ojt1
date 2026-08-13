import path from 'path';
import dotenv from 'dotenv';
import { Pool } from 'pg';

const envPath = path.resolve(__dirname, '../.env');
dotenv.config({ path: envPath });

const connectionString = process.env.DATABASE_URL;
const useSsl = process.env.DB_SSL === 'true' || process.env.NODE_ENV === 'production';

export const pool = new Pool(
  connectionString
    ? {
        connectionString,
        ssl: useSsl ? { rejectUnauthorized: false } : false,
      }
    : {
        host: process.env.DB_HOST || 'localhost',
        port: Number(process.env.DB_PORT || 5432),
        user: process.env.DB_USER || 'postgres',
        password: process.env.DB_PASS || '',
        database: process.env.DB_DATABASE || 'ojthub',
        ssl: useSsl ? { rejectUnauthorized: false } : false,
      },
);

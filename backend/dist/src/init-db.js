"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.initializeDatabase = initializeDatabase;
const db_1 = require("./db");
/**
 * Initialize the database with all required tables
 * Run this once to set up the schema
 */
async function initializeDatabase() {
    const client = await db_1.pool.connect();
    try {
        console.log('Starting database initialization...');
        // Create students table
        await client.query(`
      CREATE TABLE IF NOT EXISTS students (
        id SERIAL PRIMARY KEY,
        student_id VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        department VARCHAR(255),
        required_ojt_hours INTEGER DEFAULT 480,
        avatar TEXT,
        registration_status VARCHAR(50) DEFAULT 'pending_verification',
        enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      );
    `);
        console.log('✓ Students table created/verified');
        // Create admin_users table
        await client.query(`
      CREATE TABLE IF NOT EXISTS admin_users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        avatar TEXT,
        is_super_admin BOOLEAN DEFAULT FALSE,
        status VARCHAR(50) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      );
    `);
        console.log('✓ Admin users table created/verified');
        // Create coordinator_accounts table
        await client.query(`
      CREATE TABLE IF NOT EXISTS coordinator_accounts (
        id SERIAL PRIMARY KEY,
        access_code VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        department VARCHAR(255),
        avatar TEXT,
        status VARCHAR(50) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      );
    `);
        console.log('✓ Coordinator accounts table created/verified');
        // Create indexes
        await client.query(`CREATE INDEX IF NOT EXISTS idx_students_student_id ON students(student_id);`);
        await client.query(`CREATE INDEX IF NOT EXISTS idx_students_email ON students(email);`);
        await client.query(`CREATE INDEX IF NOT EXISTS idx_admin_users_username ON admin_users(username);`);
        await client.query(`CREATE INDEX IF NOT EXISTS idx_coordinator_access_code ON coordinator_accounts(access_code);`);
        console.log('✓ Indexes created/verified');
        console.log('✅ Database initialization completed successfully!');
    }
    catch (error) {
        console.error('❌ Error during database initialization:', error);
        throw error;
    }
    finally {
        client.release();
    }
}
// Run if this is the main module
if (require.main === module) {
    initializeDatabase()
        .then(() => {
        console.log('Database setup complete.');
        process.exit(0);
    })
        .catch((err) => {
        console.error('Database initialization failed:', err);
        process.exit(1);
    });
}

"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.createAdmin = createAdmin;
exports.createSampleCoordinator = createSampleCoordinator;
const db_1 = require("./db");
const bcryptjs_1 = __importDefault(require("bcryptjs"));
/**
 * Create an admin account
 * Usage: npx ts-node src/create-admin.ts <username> <password> <name> <email>
 * Or run with interactive prompts if no arguments provided
 */
async function createAdmin(username, password, name, email) {
    let adminUsername = username;
    let adminPassword = password;
    let adminName = name;
    let adminEmail = email;
    // If not all arguments provided, use defaults for demo
    if (!adminUsername || !adminPassword || !adminName || !adminEmail) {
        adminUsername = 'admin';
        adminPassword = 'Admin@123456';
        adminName = 'System Administrator';
        adminEmail = 'admin@ojt.local';
    }
    try {
        // Check if admin already exists
        const existingResult = await db_1.pool.query('SELECT id FROM admin_users WHERE username = $1 OR email = $2 LIMIT 1', [adminUsername, adminEmail]);
        if (existingResult.rows.length > 0) {
            console.log(`⚠️  Admin user "${adminUsername}" or email "${adminEmail}" already exists.`);
            return false;
        }
        // Hash password
        const passwordHash = await bcryptjs_1.default.hash(adminPassword, 10);
        // Create admin user
        const result = await db_1.pool.query('INSERT INTO admin_users (username, password_hash, name, email, is_super_admin, status) VALUES ($1, $2, $3, $4, $5, $6) RETURNING id', [adminUsername, passwordHash, adminName, adminEmail, true, 'active']);
        console.log('✅ Admin account created successfully!');
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        console.log(`Username: ${adminUsername}`);
        console.log(`Password: ${adminPassword}`);
        console.log(`Name: ${adminName}`);
        console.log(`Email: ${adminEmail}`);
        console.log(`ID: ${result.rows[0].id}`);
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        console.log('⚠️  IMPORTANT: Save these credentials in a secure place.');
        console.log('   Use username and password to login at /admin-login');
        return true;
    }
    catch (error) {
        console.error('❌ Error creating admin:', error);
        throw error;
    }
}
async function createSampleCoordinator() {
    const accessCode = 'COORD-' + Math.random().toString(36).substr(2, 8).toUpperCase();
    const password = 'Coord@123456';
    const fullName = 'Sample Coordinator';
    const email = 'coordinator@ojt.local';
    const department = 'Computer Science';
    try {
        // Check if coordinator already exists
        const existingResult = await db_1.pool.query('SELECT id FROM coordinator_accounts WHERE email = $1 LIMIT 1', [email]);
        if (existingResult.rows.length > 0) {
            console.log(`⚠️  Coordinator with email "${email}" already exists.`);
            return false;
        }
        const passwordHash = await bcryptjs_1.default.hash(password, 10);
        const result = await db_1.pool.query('INSERT INTO coordinator_accounts (access_code, password_hash, full_name, email, department, status) VALUES ($1, $2, $3, $4, $5, $6) RETURNING id', [accessCode, passwordHash, fullName, email, department, 'active']);
        console.log('✅ Sample Coordinator account created!');
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        console.log(`Access Code: ${accessCode}`);
        console.log(`Password: ${password}`);
        console.log(`Name: ${fullName}`);
        console.log(`Email: ${email}`);
        console.log(`Department: ${department}`);
        console.log(`ID: ${result.rows[0].id}`);
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        return true;
    }
    catch (error) {
        console.error('❌ Error creating coordinator:', error);
        throw error;
    }
}
// Run if this is the main module
if (require.main === module) {
    const args = process.argv.slice(2);
    (async () => {
        try {
            console.log('🚀 Admin Account Setup\n');
            // Create admin
            await createAdmin(args[0], args[1], args[2], args[3]);
            // Create sample coordinator
            await createSampleCoordinator();
            console.log('\n✅ Setup complete!');
            process.exit(0);
        }
        catch (err) {
            console.error('Setup failed:', err);
            process.exit(1);
        }
    })();
}

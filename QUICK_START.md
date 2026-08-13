# OJT System - Quick Start Guide ⚡

Get the system running in 5 minutes!

## Prerequisites
- Node.js 16+ and npm
- PostgreSQL running
- VS Code or any code editor

## Step 1: Install Dependencies (2 min)

```bash
# Backend
cd backend
npm install

# Frontend (in new terminal)
cd frontend
npm install
```

## Step 2: Setup Database (1 min)

```bash
# In pgAdmin or via CLI, create database:
createdb ojthub
```

## Step 3: Configure Environment Files (30 sec)

**backend/.env**
```env
NODE_ENV=development
PORT=5000
DATABASE_URL=postgresql://postgres:your_password@localhost:5432/ojthub
JWT_SECRET=your-secret-key-change-this
FRONTEND_URL=http://localhost:3000
```

**frontend/.env.local**
```env
NEXT_PUBLIC_API_URL=http://localhost:5000
```

## Step 4: Initialize & Create Admin (1 min)

```bash
cd backend

# Create database tables
npm run init-db

# Create admin account
npm run create-admin

# Save the credentials shown!
```

## Step 5: Run Both Servers (30 sec)

**Terminal 1 - Backend:**
```bash
cd backend
npm run dev
# Should see: "Backend running on http://localhost:5000"
```

**Terminal 2 - Frontend:**
```bash
cd frontend
npm run dev
# Should see: "http://localhost:3000"
```

## Step 6: Test the System! 🎉

1. Open http://localhost:3000
2. Try Student Login:
   - ID: `S123`
   - Password: `password123`
3. Or press 'q' for Admin Login:
   - Username: `admin`
   - Password: `Admin@123456`

---

## Test Accounts

### Student Account
- **ID**: S123
- **Password**: password123
- **Status**: Pre-approved, ready to use

### Admin Account
- **Username**: admin
- **Password**: Admin@123456

### Coordinator Account
- Created automatically during `npm run create-admin`
- Check terminal output for access code

---

## Common Commands

```bash
# Backend
npm run dev          # Start development server
npm run build        # Build for production
npm run init-db      # Create database tables
npm run create-admin # Create admin account

# Frontend
npm run dev          # Start Next.js dev server
npm run build        # Build for production
npm run start        # Start production server
npm run lint         # Check code quality
```

---

## Verify Everything Works

### Backend Health
```bash
curl http://localhost:5000/api/health
# Should return: {"status":"ok"}
```

### Test Login
```bash
curl -X POST http://localhost:5000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"studentId":"S123","password":"password123"}'
# Should return: {"token":"...", "profile":{...}}
```

---

## Troubleshooting

### "Cannot connect to database"
```bash
# Check PostgreSQL is running
# Windows: Services > PostgreSQL
# Mac: brew services list | grep postgres
# Linux: sudo systemctl status postgresql
```

### "Port 5000 already in use"
```bash
# Kill existing process or use different port
PORT=5001 npm run dev
```

### "Module not found"
```bash
# Reinstall dependencies
rm -rf node_modules package-lock.json
npm install
```

### "Blank dashboard after login"
1. Check browser console for errors (F12)
2. Verify backend is running
3. Check NEXT_PUBLIC_API_URL in .env.local

---

## Next: Read Full Documentation

See [DEPLOYMENT_SETUP_GUIDE.md](./DEPLOYMENT_SETUP_GUIDE.md) for:
- Detailed setup instructions
- Production deployment steps
- User role explanations
- API documentation
- Security best practices

---

## Features by Role

### 👨‍🎓 Student
- Register for OJT
- View dashboard
- Track OJT hours (coming soon)
- Submit reports (coming soon)

### 👨‍💼 Coordinator
- Manage assigned students
- Approve registrations (coming soon)
- View department reports (coming soon)

### 🔐 Admin
- View system statistics
- Manage all users (coming soon)
- Generate reports (coming soon)

---

**Need help?** Check DEPLOYMENT_SETUP_GUIDE.md or the troubleshooting section above.

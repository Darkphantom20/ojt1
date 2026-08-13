# OJT Monitoring System - Complete Setup & Deployment Guide

## 📋 Table of Contents
1. [System Overview](#system-overview)
2. [Prerequisites](#prerequisites)
3. [Local Development Setup](#local-development-setup)
4. [Database Setup](#database-setup)
5. [Admin Account Creation](#admin-account-creation)
6. [Running the Application](#running-the-application)
7. [User Authentication Flows](#user-authentication-flows)
8. [Deployment to Production](#deployment-to-production)
9. [Troubleshooting](#troubleshooting)

---

## System Overview

The OJT (On-The-Job Training) Monitoring System is a full-stack application designed to manage student internships with three main user roles:

- **Students**: Register, track OJT hours, submit reports
- **Coordinators**: Manage assigned students, approve registrations
- **Admins**: System-wide management and oversight

**Technology Stack:**
- **Backend**: Node.js/Express with TypeScript, PostgreSQL
- **Frontend**: Next.js with React, TypeScript
- **Authentication**: JWT (JSON Web Tokens)
- **Database**: PostgreSQL

---

## Prerequisites

### Required Software
- **Node.js** 16.x or higher
- **npm** 7.x or higher
- **PostgreSQL** 12.x or higher
- **Git** (for version control)

### Optional
- **Docker** (for containerized deployment)
- **Vercel CLI** (for Vercel deployments)

### Installation Instructions

**Windows:**
```bash
# Install Node.js from https://nodejs.org/
# Install PostgreSQL from https://www.postgresql.org/download/windows/
# Verify installations
node --version
npm --version
psql --version
```

**macOS (using Homebrew):**
```bash
brew install node postgresql
brew services start postgresql
```

**Linux (Ubuntu/Debian):**
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs postgresql
```

---

## Local Development Setup

### 1. Clone and Prepare Project

```bash
cd c:\xampp\htdocs\ojt1
```

### 2. Install Backend Dependencies

```bash
cd backend
npm install
```

### 3. Install Frontend Dependencies

```bash
cd ../frontend
npm install
```

### 4. Create Environment Files

**Backend:**
```bash
cd ../backend
cp .env.example .env
# Edit .env with your database credentials and JWT secret
```

**Frontend:**
```bash
cd ../frontend
cp .env.example .env.local
# Edit .env.local with your backend API URL
```

### 5. Example .env Files

**backend/.env:**
```env
NODE_ENV=development
PORT=5000

DATABASE_URL=postgresql://postgres:password@localhost:5432/ojthub
# OR individual params:
DB_HOST=localhost
DB_PORT=5432
DB_USER=postgres
DB_PASS=your_password
DB_DATABASE=ojthub
DB_SSL=false

JWT_SECRET=your-super-secret-key-generated-by-openssl

FRONTEND_URL=http://localhost:3000
FRONTEND_URLS=http://localhost:3001
```

**frontend/.env.local:**
```env
NEXT_PUBLIC_API_URL=http://localhost:5000
NEXT_PUBLIC_APP_NAME=OJT Monitoring System
NEXT_PUBLIC_DEBUG_MODE=true
```

---

## Database Setup

### 1. Create PostgreSQL Database

```bash
# Connect to PostgreSQL
psql -U postgres

# In PostgreSQL shell:
CREATE DATABASE ojthub;
\q
```

### 2. Initialize Database Schema

```bash
cd backend
npm run init-db
```

This command will:
- ✅ Create `students` table
- ✅ Create `admin_users` table
- ✅ Create `coordinator_accounts` table
- ✅ Create necessary indexes

**Output:**
```
✓ Students table created/verified
✓ Admin users table created/verified
✓ Coordinator accounts table created/verified
✓ Indexes created/verified
✅ Database initialization completed successfully!
```

---

## Admin Account Creation

### Create Admin Account (Required)

```bash
cd backend
npm run create-admin
```

This creates:
1. **Default Admin Account** with credentials:
   - Username: `admin`
   - Password: `Admin@123456`
   - Email: `admin@ojt.local`

2. **Sample Coordinator Account** with random access code

**Output:**
```
✅ Admin account created successfully!
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Username: admin
Password: Admin@123456
Name: System Administrator
Email: admin@ojt.local
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
⚠️  IMPORTANT: Save these credentials in a secure place.
   Use username and password to login at /admin-login
```

### Create Custom Admin Account

```bash
cd backend
npm run create-admin -- <username> <password> <full-name> <email>

# Example:
npm run create-admin -- superadmin MySecure@Pass123 "John Admin" john@ojt.local
```

---

## Running the Application

### Terminal 1: Start Backend Server

```bash
cd backend
npm run dev
```

**Expected Output:**
```
Inserted test student S123 / password123
✅ Database initialization completed successfully!
Backend running on http://localhost:5000
```

### Terminal 2: Start Frontend Development Server

```bash
cd frontend
npm run dev
```

**Expected Output:**
```
▲ Next.js 14.2.5
- Local:        http://localhost:3000
```

### Access the Application

Open your browser and navigate to:
- **Main Portal**: http://localhost:3000
- **Student Login**: http://localhost:3000/login
- **Admin Login**: http://localhost:3000/admin-login (or press 'q' on home page)
- **Coordinator Login**: http://localhost:3000/coordinator-login
- **Backend API**: http://localhost:5000

---

## User Authentication Flows

### Student Registration & Login

#### Registration Flow:
1. Student navigates to `/register`
2. Fills in: Student ID, Full Name, Email, Department, Password
3. System creates account with status `pending_verification`
4. Admin must approve before student can login

#### Login Flow:
1. Student goes to `/login`
2. Enters Student ID and Password
3. System verifies account status is `approved`
4. JWT token stored in localStorage
5. Redirects to `/dashboard`

#### Test Student Account:
- **ID**: `S123`
- **Password**: `password123`
- **Status**: Pre-approved
- **Access**: Immediate login available

### Admin Login

1. Admin navigates to `/admin-login` (press 'q' on home page for hidden link)
2. Enters username and password
3. JWT token stored as `ojt_admin_token`
4. Redirected to `/admin-dashboard`

**Default Credentials:**
- **Username**: `admin`
- **Password**: `Admin@123456`

### Coordinator Login

1. Coordinator goes to `/coordinator-login`
2. Enters Access Code and Password
3. JWT token stored as `ojt_coordinator_token`
4. Redirected to `/coordinator-dashboard`

---

## Deployment to Production

### Option 1: Vercel (Recommended for Frontend)

**Frontend Deployment:**
```bash
cd frontend

# Install Vercel CLI
npm install -g vercel

# Deploy
vercel
```

**Backend Deployment Options:**
1. **Vercel** - For serverless Node.js
2. **Railway** - Easy PostgreSQL + Node.js
3. **Render** - Free tier available
4. **Heroku** - Traditional PaaS

### Option 2: Railway (Full-Stack)

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login
railway login

# Create project
railway init

# Deploy
railway up
```

### Option 3: Docker Containerization

**Create backend Dockerfile:**
```dockerfile
FROM node:18-alpine
WORKDIR /app
COPY package*.json ./
RUN npm ci --only=production
COPY . .
RUN npm run build
EXPOSE 5000
CMD ["npm", "start"]
```

**Build and run:**
```bash
docker build -t ojt-backend .
docker run -p 5000:5000 --env-file .env ojt-backend
```

### Production Environment Variables

**Important Security Changes:**

```env
# backend/.env.production
NODE_ENV=production
PORT=5000

# Use PostgreSQL connection string (Supabase, AWS RDS, etc.)
DATABASE_URL=postgresql://user:pass@production-db:5432/ojthub
DB_SSL=true

# CHANGE THESE FROM DEFAULTS!
JWT_SECRET=generate-strong-random-key-here-openssl-rand-base64-32

# Production frontend URLs
FRONTEND_URL=https://yourdomain.com
FRONTEND_URLS=https://www.yourdomain.com

# Optional: Email service
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password

# Logging
LOG_LEVEL=info
ENABLE_API_LOGGING=false
```

```env
# frontend/.env.production
NEXT_PUBLIC_API_URL=https://api.yourdomain.com
NEXT_PUBLIC_APP_NAME=OJT Monitoring System
NEXT_PUBLIC_DEBUG_MODE=false
```

### SSL/HTTPS Setup

For production:
1. Register domain name
2. Configure DNS records to point to server
3. Install SSL certificate:
   - **Vercel**: Automatic
   - **Railway/Render**: Automatic
   - **Self-hosted**: Use Let's Encrypt with Certbot

### Database Backup Strategy

```bash
# Backup PostgreSQL database
pg_dump ojthub > backup-$(date +%Y%m%d).sql

# Restore from backup
psql ojthub < backup-20240101.sql
```

---

## Troubleshooting

### Common Issues

#### 1. Database Connection Errors

**Error:** `Error: connect ECONNREFUSED 127.0.0.1:5432`

**Solution:**
```bash
# Check PostgreSQL is running
# Windows:
Get-Service postgresql*

# Mac:
brew services list | grep postgres

# Linux:
sudo systemctl status postgresql

# Start PostgreSQL if not running:
# Mac: brew services start postgresql
# Linux: sudo systemctl start postgresql
```

#### 2. Port Already in Use

**Error:** `Error: listen EADDRINUSE: address already in use :::5000`

**Solution:**
```bash
# Windows: Kill process on port 5000
netstat -ano | findstr :5000
taskkill /PID <PID> /F

# Mac/Linux:
lsof -i :5000
kill -9 <PID>

# Or use different port:
PORT=5001 npm run dev
```

#### 3. CORS Errors

**Error:** `Access to XMLHttpRequest blocked by CORS policy`

**Solution:**
- Ensure `FRONTEND_URL` in backend .env includes your frontend URL
- Check frontend `NEXT_PUBLIC_API_URL` matches backend URL
- Clear browser cache and cookies

#### 4. JWT Token Issues

**Error:** `Invalid token` or authentication failing

**Solution:**
```bash
# Verify JWT_SECRET is set in backend .env
# Check browser localStorage for tokens:
# Open DevTools > Application > Local Storage

# If corrupted, clear localStorage:
# In browser console:
localStorage.clear();
// Then logout and login again
```

#### 5. Node Modules Issues

**Error:** `Cannot find module` or strange build errors

**Solution:**
```bash
# Reinstall dependencies
rm -rf node_modules package-lock.json
npm install

# Clear Next.js cache
rm -rf .next
npm run build
```

### Getting Help

1. Check backend logs: `npm run dev` output
2. Check frontend console: Browser DevTools > Console
3. Check database: `psql -U postgres -d ojthub -c "SELECT * FROM students;"`
4. Check network requests: Browser DevTools > Network tab

---

## API Documentation

### Authentication Endpoints

**Student Login:**
```
POST /api/auth/login
Body: { studentId: string, password: string }
Response: { token: string, profile: {...} }
```

**Admin Login:**
```
POST /api/admin/login
Body: { username: string, password: string }
Response: { token: string, profile: {...} }
```

**Coordinator Login:**
```
POST /api/coordinator/login
Body: { accessCode: string, password: string }
Response: { token: string, profile: {...} }
```

### Protected Endpoints

All protected endpoints require:
```
Authorization: Bearer <token>
```

**Student Profile:**
```
GET /api/auth/profile
```

**Admin Dashboard:**
```
GET /api/admin/dashboard
```

**Coordinator Profile:**
```
GET /api/coordinator/profile
```

---

## Security Checklist

- [ ] Change default admin password
- [ ] Set strong JWT_SECRET (use `openssl rand -base64 32`)
- [ ] Enable HTTPS in production
- [ ] Configure proper CORS origins
- [ ] Enable database SSL for production
- [ ] Set NODE_ENV=production
- [ ] Disable debug logging in production
- [ ] Implement rate limiting on login endpoints
- [ ] Set up regular database backups
- [ ] Keep dependencies updated (`npm audit fix`)

---

## Next Steps

1. **User Approval System**: Implement coordinator approval flow for pending students
2. **OJT Hours Tracking**: Add functionality to track and log student hours
3. **Report Generation**: Create system for students to submit daily/weekly reports
4. **Email Notifications**: Implement email alerts for approvals
5. **Mobile App**: Develop mobile application for on-the-go tracking
6. **Analytics Dashboard**: Add advanced reporting and analytics

---

## Support & Maintenance

For issues or questions:
1. Review this documentation
2. Check the troubleshooting section
3. Review backend/frontend logs
4. Check database integrity

**Last Updated**: 2024
**System Version**: 1.0.0

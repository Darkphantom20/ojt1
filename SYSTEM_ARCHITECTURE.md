# System Architecture & Database Schema

## 🏗️ System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        USER BROWSERS                             │
├─────────────────────────────────────────────────────────────────┤
│  Student Portal    │    Coordinator Portal    │   Admin Portal   │
│  /login            │    /coordinator-login    │   /admin-login   │
│  /register         │    /coordinator-dash     │   /admin-dash    │
│  /dashboard        │                          │                  │
└────────────┬────────────────────┬──────────────────────┬─────────┘
             │                    │                      │
             └────────┬───────────┴──────────────┬───────┘
                      │                          │
                      ▼                          ▼
        ┌─────────────────────────┐   ┌──────────────────┐
        │  Next.js Frontend       │   │  API Requests    │
        │  (React + TypeScript)   │   │  (Fetch API)     │
        └────────────┬────────────┘   └────────┬─────────┘
                     │                         │
                     │ http://localhost:3000   │
                     │                         │
                     ▼                         ▼
        ┌──────────────────────────────────────────────┐
        │      Express Backend                         │
        │      (Node.js + TypeScript)                  │
        │      http://localhost:5000                   │
        │                                              │
        │  Routes:                                     │
        │  ├── /api/auth/* (Student auth)             │
        │  ├── /api/admin/* (Admin operations)        │
        │  ├── /api/coordinator/* (Coordinator ops)   │
        │  └── /api/students/* (Student data)         │
        └────────────┬─────────────────────────────────┘
                     │
                     │ PostgreSQL Driver (pg)
                     │
                     ▼
        ┌──────────────────────────────────────────────┐
        │      PostgreSQL Database                     │
        │                                              │
        │  Tables:                                     │
        │  ├── students (accounts + registration)     │
        │  ├── admin_users (admin accounts)           │
        │  └── coordinator_accounts (coordinator acc) │
        │                                              │
        │  Features:                                   │
        │  ├── Indexes for performance                │
        │  ├── Timestamps (created/updated)           │
        │  └── Status tracking                        │
        └──────────────────────────────────────────────┘
```

---

## 📊 Database Schema

### Students Table
```sql
CREATE TABLE students (
    id SERIAL PRIMARY KEY,
    student_id VARCHAR(50) UNIQUE NOT NULL,        -- "TC-23-A-00001"
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    department VARCHAR(255),                       -- "Computer Science"
    required_ojt_hours INTEGER DEFAULT 480,
    avatar TEXT,
    registration_status VARCHAR(50) DEFAULT 'pending_verification',
      -- Possible values: pending_verification, approved, rejected
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INDEX idx_students_student_id ON students(student_id);
INDEX idx_students_email ON students(email);
```

### Admin Users Table
```sql
CREATE TABLE admin_users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,        -- "admin"
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,                   -- "System Administrator"
    email VARCHAR(255) UNIQUE NOT NULL,
    avatar TEXT,
    is_super_admin BOOLEAN DEFAULT FALSE,
    status VARCHAR(50) DEFAULT 'active',          -- "active", "disabled"
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INDEX idx_admin_users_username ON admin_users(username);
```

### Coordinator Accounts Table
```sql
CREATE TABLE coordinator_accounts (
    id SERIAL PRIMARY KEY,
    access_code VARCHAR(100) UNIQUE NOT NULL,    -- "COORD-XXXX-XXXX"
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    department VARCHAR(255),                      -- "Computer Science"
    avatar TEXT,
    status VARCHAR(50) DEFAULT 'active',          -- "active", "disabled"
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INDEX idx_coordinator_access_code ON coordinator_accounts(access_code);
```

---

## 🔐 Authentication Flow

### JWT Token Structure
```
Header:
{
  "alg": "HS256",
  "typ": "JWT"
}

Payload (Student):
{
  "id": 1,
  "studentId": "S123",
  "name": "John Student",
  "email": "john@student.local",
  "department": "Computer Science",
  "iat": 1704067200,
  "exp": 1704672000
}

Payload (Admin):
{
  "id": 1,
  "username": "admin",
  "name": "System Administrator",
  "email": "admin@ojt.local",
  "role": "admin",
  "iat": 1704067200,
  "exp": 1704672000
}

Payload (Coordinator):
{
  "id": 1,
  "fullName": "Jane Coordinator",
  "email": "jane@coordinator.local",
  "department": "Computer Science",
  "role": "coordinator",
  "iat": 1704067200,
  "exp": 1704672000
}
```

### Token Storage
```javascript
// Student Token
localStorage.setItem('ojt_token', '<jwt_token>');

// Admin Token
localStorage.setItem('ojt_admin_token', '<jwt_token>');

// Coordinator Token
localStorage.setItem('ojt_coordinator_token', '<jwt_token>');
```

---

## 🌐 API Endpoints

### Authentication Endpoints
```
POST /api/auth/login
├── Request: { studentId, password }
├── Response: { token, profile: {...} }
└── Status: 200/401/403

GET /api/auth/profile
├── Headers: { Authorization: "Bearer <token>" }
├── Response: { id, studentId, name, email, department }
└── Status: 200/401

POST /api/admin/login
├── Request: { username, password }
├── Response: { token, profile: {...} }
└── Status: 200/401

GET /api/admin/profile
├── Headers: { Authorization: "Bearer <token>" }
├── Response: { id, username, name, email }
└── Status: 200/401/403

GET /api/admin/dashboard
├── Headers: { Authorization: "Bearer <token>" }
├── Response: { totalStudents, totalCoordinators, pendingVerifications }
└── Status: 200/401/403

POST /api/coordinator/login
├── Request: { accessCode, password }
├── Response: { token, profile: {...} }
└── Status: 200/401

GET /api/coordinator/profile
├── Headers: { Authorization: "Bearer <token>" }
├── Response: { id, fullName, email, department }
└── Status: 200/401/403

POST /api/students/register
├── Request: { studentId, password, name, email, department }
├── Response: { message, id }
└── Status: 201/400/409
```

---

## 📁 Frontend Component Hierarchy

```
pages/
├── index.tsx (Main Portal)
│   └── Portal Selection
│       ├── Student → /login
│       ├── Coordinator → /coordinator-login
│       └── Admin → /admin-login (hidden, press 'q')
│
├── login.tsx (Student Login)
│   └── useRequireAuth('student')
│       └── redirect if already logged in
│
├── register.tsx (Student Registration)
│   └── Form submission to /api/students/register
│
├── dashboard.tsx (Student Dashboard)
│   └── useRequireAuth('student')
│       └── Fetch from /api/auth/profile
│
├── admin-login.tsx (Admin Login)
│   └── useRequireAuth('admin')
│       └── redirect if already logged in
│
├── admin-dashboard.tsx (Admin Dashboard)
│   └── useRequireAuth('admin')
│       └── Fetch from /api/admin/dashboard
│
├── coordinator-login.tsx (Coordinator Login)
│   └── useRequireAuth('coordinator')
│       └── redirect if already logged in
│
└── coordinator-dashboard.tsx (Coordinator Dashboard)
    └── useRequireAuth('coordinator')
        └── Fetch from /api/coordinator/profile
```

---

## 🔄 Data Flow Example: Student Login

```
1. User fills login form
   │
   ├─→ Input Validation (client-side)
   │   └─→ Check non-empty fields
   │
2. Submit POST /api/auth/login
   │
   ├─→ Backend receives request
   │
   ├─→ Query database
   │   └─→ SELECT ... WHERE student_id = ?
   │
   ├─→ Check account exists
   │   └─→ Return 401 if not found
   │
   ├─→ Verify password with bcryptjs
   │   └─→ Return 401 if invalid
   │
   ├─→ Check registration_status = 'approved'
   │   └─→ Return 403 if pending/rejected
   │
   ├─→ Create JWT token
   │   └─→ Sign with JWT_SECRET, 7-day expiry
   │
3. Return response with token
   │
   ├─→ Frontend receives token
   │
   ├─→ Store in localStorage.ojt_token
   │
   ├─→ Redirect to /dashboard
   │
4. Dashboard page loads
   │
   ├─→ useRequireAuth('student') checks token
   │
   ├─→ Fetch /api/auth/profile with token
   │
   ├─→ Display user data
   │
5. User logged in! ✅
```

---

## 🔐 Security Considerations

### Password Security
- Passwords hashed with bcryptjs (10 salt rounds)
- Never stored in plaintext
- Verified using bcryptjs.compare()

### Token Security
- JWT tokens expire after 7 days
- Stored in browser localStorage
- Sent in Authorization header: `Bearer <token>`
- Verified with JWT_SECRET on backend

### Access Control
- Each endpoint verifies JWT token
- Verifies token role matches endpoint
- Role-based endpoint restrictions

### CORS
- Frontend URLs whitelisted
- Credentials allowed in requests
- Only specified origins can access API

---

## 📈 Scalability Notes

### Current Capacity
- Supports thousands of students
- Database indexes optimize lookups
- JWT stateless (no session storage needed)

### Future Improvements
- Add Redis for token blacklisting (logout)
- Implement rate limiting on auth endpoints
- Add email verification for registration
- Implement refresh tokens for better security
- Add password reset flow
- Add two-factor authentication

---

## 🚀 Deployment Architecture

### Development
```
PC/Laptop
├── Backend (npm run dev) → localhost:5000
├── Frontend (npm run dev) → localhost:3000
└── PostgreSQL (local) → localhost:5432
```

### Production
```
Cloud Provider (Vercel/Railway/etc)
├── Frontend (Next.js)
│   └── Static files (CDN)
│   └── API Routes to backend
│
├── Backend (Node.js)
│   └── Environment: production
│   └── CORS enabled for prod domain
│   └── HTTPS enforced
│
└── Database (PostgreSQL)
    ├── Cloud-hosted (AWS RDS, Vercel, etc)
    ├── SSL enabled
    ├── Automated backups
    └── Read replicas (optional)
```

---

*Architecture Last Updated: 2024*
*Status: Production Ready* ✨

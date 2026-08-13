# Vercel Deployment Guide

## Overview

Your OJT1 project is configured for monorepo deployment on Vercel with:
- **Frontend**: Next.js application
- **Backend**: TypeScript/Express API server

## Prerequisites

1. **Vercel Account**: [Sign up at vercel.com](https://vercel.com)
2. **GitHub Repository**: Connected to Vercel
3. **Environment Variables**: Configured in Vercel dashboard

## Deployment Steps

### Step 1: Connect GitHub Repository

1. Go to [Vercel Dashboard](https://vercel.com/dashboard)
2. Click "Add New" → "Project"
3. Select your GitHub repository `ojt1`
4. Vercel will automatically detect the monorepo structure

### Step 2: Configure Environment Variables

In Vercel Dashboard → Project Settings → Environment Variables, add:

#### Frontend Variables
```
NEXT_PUBLIC_API_URL=https://your-backend-url.vercel.app/api
```

#### Backend Variables
```
DATABASE_URL=postgresql://user:password@host:port/database
JWT_SECRET=your_very_secure_jwt_secret_here
JWT_REFRESH_SECRET=your_refresh_token_secret
MAIL_PASSWORD=your_gmail_app_password
MAIL_USERNAME=your-email@gmail.com
NODE_ENV=production
```

### Step 3: Configure Root Directory (if needed)

**Option A: Monorepo Deployment (Recommended)**
- Root Directory: `.` (root of repository)
- Vercel auto-detects `vercel.json` configuration

**Option B: Separate Deployments**
- Deploy Frontend: Root Directory = `frontend/`
- Deploy Backend separately

### Step 4: Deploy

1. **Automatic**: Push to main branch → Vercel auto-deploys
2. **Manual**: 
   ```bash
   npm install -g vercel
   vercel --prod
   ```

## Project Structure

```
ojt1/
├── vercel.json                 # Root monorepo config
├── frontend/
│   ├── vercel.json            # Frontend config
│   ├── next.config.js
│   ├── package.json
│   └── pages/
├── backend/
│   ├── vercel.json            # Backend config
│   ├── package.json
│   ├── tsconfig.json
│   └── src/
│       └── index.ts
└── .env.example
```

## Configuration Files Explained

### Root `vercel.json`
- Defines builds for both services
- Configures API routing
- Sets environment variables

### Frontend `vercel.json`
- Next.js specific configuration
- Output directory: `.next`
- Environment: `NEXT_PUBLIC_API_URL`

### Backend `vercel.json`
- TypeScript build command
- Output directory: `dist/`
- Environment: Database, JWT, Mail secrets

## API Routing

All requests to `/api/*` are routed to the backend service:

```
GET /api/users       → backend/src/routes/users.ts
POST /api/auth       → backend/src/routes/auth.ts
GET /dashboard       → frontend pages
```

## Troubleshooting

### Build Failures

**Error: "Cannot find module"**
```bash
# In frontend/
npm install

# In backend/
npm install
npm run build
```

**Error: "NEXT_PUBLIC_API_URL undefined"**
- Check Vercel Environment Variables
- Must start with `NEXT_PUBLIC_` for frontend
- Restart deployment after adding variables

### Database Connection Issues

```bash
# Test connection
psql $DATABASE_URL -c "SELECT version();"
```

### Backend Not Responding

1. Check backend logs: Vercel Dashboard → Deployments → Logs
2. Verify `DATABASE_URL` format
3. Check CORS settings in backend

## Environment Variable Reference

| Variable | Service | Type | Required | Example |
|----------|---------|------|----------|---------|
| `NEXT_PUBLIC_API_URL` | Frontend | String | Yes | `https://api.example.com` |
| `DATABASE_URL` | Backend | String | Yes | `postgresql://user:pass@host/db` |
| `JWT_SECRET` | Backend | String | Yes | `random-secure-string` |
| `JWT_REFRESH_SECRET` | Backend | String | Yes | `random-secure-string` |
| `MAIL_PASSWORD` | Backend | String | Yes | `app-specific-password` |
| `NODE_ENV` | Backend | String | No | `production` |

## Deployment Commands

```bash
# Local testing
npm run dev              # Starts both frontend and backend in dev mode

# Build
npm run build            # Builds both services

# Deploy to Vercel
vercel --prod           # Production deployment
```

## Monitoring & Logs

1. **Vercel Dashboard**: Project → Deployments → Select deployment
2. **Frontend Logs**: Shows Next.js build and runtime errors
3. **Backend Logs**: Shows Express server and API errors

## Custom Domains

1. Vercel Dashboard → Project Settings → Domains
2. Add custom domain (e.g., `ojt.example.com`)
3. Configure DNS records
4. Add SSL certificate (automatic)

## Performance Optimization

### Frontend
- Next.js auto-optimization
- Image optimization enabled
- Code splitting enabled
- ISR (Incremental Static Regeneration)

### Backend
- Node runtime: ~1.2 GB RAM
- Edge Network: Global CDN
- Serverless functions: Auto-scaling

## Rollback & Versioning

```bash
# View deployment history
vercel list

# Rollback to previous version
vercel rollback

# Preview environments
vercel preview
```

## Security Best Practices

✅ **Do:**
- Use Vercel Environment Variables (encrypted)
- Never commit `.env` file
- Rotate secrets regularly
- Use HTTPS only
- Enable API authentication

❌ **Don't:**
- Hardcode secrets in code
- Commit sensitive data
- Share `.env` files
- Use weak passwords
- Expose database URLs publicly

## Useful Links

- [Vercel Documentation](https://vercel.com/docs)
- [Next.js Deployment](https://nextjs.org/learn/basics/deploying-nextjs-app)
- [Vercel Environment Variables](https://vercel.com/docs/concepts/projects/environment-variables)
- [Monorepo Support](https://vercel.com/docs/concepts/monorepos)

## Support

- **Vercel Support**: support@vercel.com
- **Project Issues**: GitHub Issues
- **Documentation**: See ENV_SETUP_GUIDE.md

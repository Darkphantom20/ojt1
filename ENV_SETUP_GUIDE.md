# Environment Configuration Guide

## ⚠️ SECURITY IMPORTANT

**NEVER commit the `.env` file to Git!** It contains sensitive credentials and secrets.

The `.env` file is already listed in `.gitignore` and will NOT be pushed to GitHub. Only `.env.example` should be in the repository.

## Setup Instructions

### 1. Create Local .env File

```bash
# Copy the example file to create your local .env
cp .env.example .env
```

### 2. Critical Security Variables

**Update these values in your `.env` file:**

#### Database Credentials
- `DB_HOST` - Your database server (default: localhost)
- `DB_PORT` - Database port (default: 3306 for MySQL)
- `DB_USER` - Database username (default: root)
- `DB_PASS` - Database password (CHANGE THIS!)
- `DATABASE_URL` - PostgreSQL/Supabase connection string

#### JWT & Authentication
- `JWT_SECRET` - Generate a strong secret key
  ```bash
  # Generate a random JWT secret
  openssl rand -base64 32
  ```
- `JWT_REFRESH_SECRET` - Another strong secret for refresh tokens

#### Email Configuration
- `MAIL_USERNAME` - Your Gmail or SMTP email
- `MAIL_PASSWORD` - App-specific password (NOT your Gmail password)
  - For Gmail: [Create App Password](https://myaccount.google.com/apppasswords)

#### API Keys
- `GOOGLE_MAPS_API_KEY` - Get from [Google Cloud Console](https://console.cloud.google.com/)
- `GOOGLE_CLIENT_ID` - OAuth2 client ID
- `GOOGLE_CLIENT_SECRET` - OAuth2 client secret

### 3. Environment-Specific Setup

#### Development (.env)
```
APP_ENV=development
APP_DEBUG=false
LOG_LEVEL=debug
ENABLE_API_LOGGING=true
```

#### Production
```
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
ENABLE_API_LOGGING=false
```

### 4. Generate Secure Secrets

```bash
# Generate JWT Secret
openssl rand -base64 32

# Generate App Key (for Laravel apps)
php artisan key:generate

# Generate random password
openssl rand -base64 20
```

### 5. Verify .env is Protected

Check that `.env` is in `.gitignore`:
```bash
git check-ignore .env
# Output: .env
```

### 6. Environment Variables Reference

| Variable | Type | Purpose | Example |
|----------|------|---------|---------|
| `APP_ENV` | string | Environment type | development, production |
| `APP_DEBUG` | boolean | Debug mode | false (for production) |
| `APP_URL` | string | Application URL | http://localhost/ojt1 |
| `DB_HOST` | string | Database host | localhost |
| `JWT_SECRET` | string | JWT signing key | very-long-random-string |
| `MAIL_PASSWORD` | string | Email app password | app-specific-password |
| `GOOGLE_MAPS_API_KEY` | string | Maps API key | AIza... |

## ⚠️ Do NOT

- ❌ Commit `.env` file to Git
- ❌ Share `.env` file over email or chat
- ❌ Use weak or default passwords
- ❌ Use production secrets in development
- ❌ Commit API keys directly in code
- ❌ Store sensitive data in `.env.example`

## ✅ Do

- ✅ Create `.env` from `.env.example` locally
- ✅ Use strong, unique secrets for each environment
- ✅ Rotate secrets regularly
- ✅ Use environment-specific secrets (dev, staging, prod)
- ✅ Share `.env.example` with clear instructions
- ✅ Use `.env.local` for local overrides

## Verification

```bash
# Check git status to ensure .env is not staged
git status

# Verify .env exists locally
ls -la .env

# Verify .env is in .gitignore
cat .gitignore | grep ".env"
```

## Troubleshooting

**"Connection refused" errors:**
- Verify database is running and credentials are correct

**"Unauthorized" errors:**
- Check JWT_SECRET and API keys are correctly set
- Verify email credentials for mail settings

**"Service unavailable" errors:**
- Check API endpoints (FRONTEND_URL, BACKEND_URL)
- Verify external services (Google Maps, etc.) are accessible

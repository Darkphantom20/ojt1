import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/router';
import { apiBase } from '../lib/api';
import { getLoggedInUserType, AUTH_TOKENS } from '../lib/auth';

export default function AdminLogin() {
  const router = useRouter();
  const [adminId, setAdminId] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  // Check if already logged in
  useEffect(() => {
    if (getLoggedInUserType() === 'admin') {
      router.push('/admin-dashboard');
    }
  }, [router]);

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError('');
    setIsLoading(true);

    if (!adminId.trim() || !password.trim()) {
      setError('Username and password are required.');
      setIsLoading(false);
      return;
    }

    try {
      const response = await fetch(`${apiBase}/api/admin/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: adminId, password }),
      });
      const data = await response.json();
      if (!response.ok) {
        setError(data.message || 'Login failed');
        setIsLoading(false);
        return;
      }
      localStorage.setItem(AUTH_TOKENS.ADMIN, data.token);
      window.location.href = '/admin-dashboard';
    } catch (err) {
      setError('Unable to reach the backend. Make sure the API server is running.');
      setIsLoading(false);
    }
  }

  return (
    <main className="hero-shell">
      <div className="auth-card" style={{ maxWidth: 520, margin: '3rem auto' }}>
        <div className="page-header">
          <h1>Admin Login</h1>
          <p className="small-note">Use your admin credentials to access the system control panel.</p>
        </div>
        <form onSubmit={handleSubmit} className="form-block">
          <label>
            Admin ID
            <input
              type="text"
              autoComplete="username"
              value={adminId}
              onChange={(event) => setAdminId(event.target.value)}
              placeholder="admin"
              disabled={isLoading}
            />
          </label>
          <label>
            Password
            <input
              type="password"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              placeholder="••••••••"
              disabled={isLoading}
            />
          </label>
          <button type="submit" className="primary" disabled={isLoading}>
            {isLoading ? 'Logging in...' : 'Login'}
          </button>
          {error && <div className="feedback">{error}</div>}
        </form>
        <p className="support-note">
          <Link href="/">Back to main menu</Link>
        </p>
      </div>
    </main>
  );
}

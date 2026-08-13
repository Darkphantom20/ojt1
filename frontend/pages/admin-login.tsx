import { useState } from 'react';
import Link from 'next/link';
import { apiBase } from '../lib/api';

export default function AdminLogin() {
  const [adminId, setAdminId] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError('');

    try {
      const response = await fetch(`${apiBase}/api/admin/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: adminId, password }),
      });
      const data = await response.json();
      if (!response.ok) {
        setError(data.message || 'Login failed');
        return;
      }
      localStorage.setItem('ojt_admin_token', data.token);
      window.location.href = '/admin-dashboard';
    } catch (err) {
      setError('Unable to reach the backend');
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
            />
          </label>
          <label>
            Password
            <input type="password" value={password} onChange={(event) => setPassword(event.target.value)} placeholder="••••••••" />
          </label>
          <button type="submit" className="primary">Login</button>
          {error && <div className="feedback">{error}</div>}
        </form>
        <p className="support-note">
          <Link href="/">Back to main menu</Link>
        </p>
      </div>
    </main>
  );
}

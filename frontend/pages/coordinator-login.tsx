import { useState } from 'react';
import Link from 'next/link';
import { apiBase } from '../lib/api';

export default function CoordinatorLogin() {
  const [accessCode, setAccessCode] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError('');

    try {
      const response = await fetch(`${apiBase}/api/coordinator/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ accessCode, password }),
      });
      const data = await response.json();
      if (!response.ok) {
        setError(data.message || 'Login failed');
        return;
      }
      localStorage.setItem('ojt_coordinator_token', data.token);
      window.location.href = '/coordinator-dashboard';
    } catch (err) {
      setError('Unable to reach the backend');
    }
  }

  return (
    <main className="hero-shell">
      <div className="auth-card" style={{ maxWidth: 520, margin: '3rem auto' }}>
        <div className="page-header">
          <h1>Coordinator Login</h1>
          <p className="small-note">Sign in with your access code to manage assigned students.</p>
        </div>
        <form onSubmit={handleSubmit} className="form-block">
          <label>
            Access Code
            <input
              type="text"
              autoComplete="username"
              value={accessCode}
              onChange={(event) => setAccessCode(event.target.value)}
              placeholder="COORD-XXXX-XXXX"
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

import { useState } from 'react';
import Link from 'next/link';
import { apiBase } from '../lib/api';

export default function Login() {
  const [studentId, setStudentId] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError('');

    try {
      const response = await fetch(`${apiBase}/api/auth/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ studentId, password }),
      });
      const data = await response.json();
      if (!response.ok) {
        setError(data.message || 'Login failed');
        return;
      }
      window.localStorage.setItem('ojt_token', data.token);
      window.location.href = '/dashboard';
    } catch (err) {
      setError('Unable to reach the backend');
    }
  }

  return (
    <main className="hero-shell">
      <div className="login-card">
        <div className="page-header">
          <h1>Student Login</h1>
          <p className="small-note">Enter your student credentials to access your OJT dashboard.</p>
        </div>
        <form onSubmit={handleSubmit} className="form-block">
          <label>
            Student ID
            <input
              type="text"
              autoComplete="username"
              value={studentId}
              onChange={(event) => setStudentId(event.target.value)}
              placeholder="TC-23-A-00001"
            />
          </label>
          <label>
            Password
            <input
              type="password"
              autoComplete="current-password"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              placeholder="••••••••"
            />
          </label>
          <button type="submit" className="primary">Login</button>
          {error && <div className="feedback">{error}</div>}
        </form>
        <div className="support-links">
          <Link href="/register">Create student account</Link>
          <Link href="/">Back to main menu</Link>
        </div>
      </div>
    </main>
  );
}

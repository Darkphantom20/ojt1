import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/router';
import { apiBase } from '../lib/api';
import { getLoggedInUserType, AUTH_TOKENS } from '../lib/auth';

export default function CoordinatorLogin() {
  const router = useRouter();
  const [accessCode, setAccessCode] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  // Check if already logged in
  useEffect(() => {
    if (getLoggedInUserType() === 'coordinator') {
      router.push('/coordinator-dashboard');
    }
  }, [router]);

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError('');
    setIsLoading(true);

    if (!accessCode.trim() || !password.trim()) {
      setError('Access code and password are required.');
      setIsLoading(false);
      return;
    }

    try {
      const response = await fetch(`${apiBase}/api/coordinator/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ accessCode, password }),
      });
      const data = await response.json();
      if (!response.ok) {
        setError(data.message || 'Login failed');
        setIsLoading(false);
        return;
      }
      localStorage.setItem(AUTH_TOKENS.COORDINATOR, data.token);
      window.location.href = '/coordinator-dashboard';
    } catch (err) {
      setError('Unable to reach the backend. Make sure the API server is running.');
      setIsLoading(false);
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

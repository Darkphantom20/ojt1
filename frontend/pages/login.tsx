import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/router';
import { apiBase } from '../lib/api';
import { getLoggedInUserType, AUTH_TOKENS } from '../lib/auth';

export default function Login() {
  const router = useRouter();
  const [studentId, setStudentId] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  // Check if already logged in
  useEffect(() => {
    if (getLoggedInUserType() === 'student') {
      router.push('/dashboard');
    }
  }, [router]);

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError('');
    setIsLoading(true);

    if (!studentId.trim() || !password.trim()) {
      setError('Student ID and password are required.');
      setIsLoading(false);
      return;
    }

    try {
      const response = await fetch(`${apiBase}/api/auth/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ studentId, password }),
      });
      const data = await response.json();
      if (!response.ok) {
        setError(data.message || 'Login failed');
        setIsLoading(false);
        return;
      }
      
      // Store token and redirect to dashboard
      localStorage.setItem(AUTH_TOKENS.STUDENT, data.token);
      window.location.href = '/dashboard';
    } catch (err) {
      setError('Unable to reach the backend. Make sure the API server is running.');
      setIsLoading(false);
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
              disabled={isLoading}
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
              disabled={isLoading}
            />
          </label>
          <button type="submit" className="primary" disabled={isLoading}>
            {isLoading ? 'Logging in...' : 'Login'}
          </button>
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

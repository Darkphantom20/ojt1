import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/router';
import { apiBase } from '../lib/api';
import { getLoggedInUserType } from '../lib/auth';

export default function Register() {
  const router = useRouter();
  const [studentId, setStudentId] = useState('');
  const [password, setPassword] = useState('');
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [department, setDepartment] = useState('');
  const [message, setMessage] = useState('');
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
    setMessage('');
    setIsLoading(true);

    if (!studentId.trim() || !password.trim() || !name.trim() || !email.trim() || !department.trim()) {
      setError('All fields are required.');
      setIsLoading(false);
      return;
    }

    if (password.length < 8) {
      setError('Password must be at least 8 characters long.');
      setIsLoading(false);
      return;
    }

    try {
      const response = await fetch(`${apiBase}/api/students/register`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ studentId, password, name, email, department }),
      });
      const data = await response.json();
      if (!response.ok) {
        setError(data.message || 'Registration failed');
        setIsLoading(false);
        return;
      }
      setMessage(
        data.message ||
        'Registration submitted successfully! Your account is pending approval. You will be notified when it is approved.',
      );
      setStudentId('');
      setPassword('');
      setName('');
      setEmail('');
      setDepartment('');
    } catch (err) {
      setError('Unable to reach the backend. Make sure the API server is running.');
    } finally {
      setIsLoading(false);
    }
  }

  return (
    <main className="hero-shell">
      <div className="auth-card" style={{ maxWidth: 620, margin: '3rem auto' }}>
        <div className="page-header">
          <h1>Student Registration</h1>
          <p className="small-note">Create your OJT account and submit your registration for coordinator approval.</p>
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
            Full Name
            <input
              type="text"
              autoComplete="name"
              value={name}
              onChange={(event) => setName(event.target.value)}
              placeholder="Juan Dela Cruz"
              disabled={isLoading}
            />
          </label>
          <label>
            Email
            <input
              type="email"
              value={email}
              onChange={(event) => setEmail(event.target.value)}
              placeholder="name@example.com"
              disabled={isLoading}
            />
          </label>
          <label>
            Department
            <input
              value={department}
              onChange={(event) => setDepartment(event.target.value)}
              placeholder="College / Department"
              disabled={isLoading}
            />
          </label>
          <label>
            Password
            <input
              type="password"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              placeholder="Minimum 8 characters"
              disabled={isLoading}
            />
          </label>
          <button type="submit" className="primary" disabled={isLoading}>
            {isLoading ? 'Registering...' : 'Register'}
          </button>
          {message && <div className="feedback success">{message}</div>}
          {error && <div className="feedback">{error}</div>}
        </form>
        <p className="support-note">
          Already registered? <Link href="/login">Login</Link>
        </p>
      </div>
    </main>
  );
}

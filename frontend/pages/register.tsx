import { useState } from 'react';
import Link from 'next/link';
import { apiBase } from '../lib/api';

export default function Register() {
  const [studentId, setStudentId] = useState('');
  const [password, setPassword] = useState('');
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [department, setDepartment] = useState('');
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError('');
    setMessage('');

    try {
      const response = await fetch(`${apiBase}/api/students/register`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ studentId, password, name, email, department }),
      });
      const data = await response.json();
      if (!response.ok) {
        setError(data.message || 'Registration failed');
        return;
      }
      setMessage(data.message || 'Registration submitted. Please verify your email.');
      setStudentId('');
      setPassword('');
      setName('');
      setEmail('');
      setDepartment('');
    } catch (err) {
      setError('Unable to reach the backend');
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
            />
          </label>
          <label>
            Email
            <input type="email" value={email} onChange={(event) => setEmail(event.target.value)} placeholder="name@example.com" />
          </label>
          <label>
            Department
            <input value={department} onChange={(event) => setDepartment(event.target.value)} placeholder="College / Department" />
          </label>
          <label>
            Password
            <input type="password" value={password} onChange={(event) => setPassword(event.target.value)} placeholder="Minimum 8 characters" />
          </label>
          <button type="submit" className="primary">Register</button>
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

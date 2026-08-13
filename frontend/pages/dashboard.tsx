import { useEffect, useState } from 'react';
import { apiBase } from '../lib/api';
import { getDeptColor } from '../lib/departmentColors';

interface StudentProfile {
  id: number;
  studentId: string;
  name: string;
  email: string;
  department: string;
  requiredOjtHours: number;
  avatar: string;
}

export default function Dashboard() {
  const [profile, setProfile] = useState<StudentProfile | null>(null);
  const [error, setError] = useState('');

  useEffect(() => {
    async function fetchProfile() {
      const token = window.localStorage.getItem('ojt_token');
      if (!token) {
        setError('Login required.');
        return;
      }

      try {
        const response = await fetch(`${apiBase}/api/auth/profile`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        if (!response.ok) {
          setError('Unable to load profile. Please login again.');
          return;
        }
        const data = await response.json();
        setProfile(data);
      } catch (err) {
        setError('Unable to connect to backend.');
      }
    }
    fetchProfile();
  }, []);

  function logout() {
    window.localStorage.removeItem('ojt_token');
    window.location.href = '/login';
  }

  if (error) {
    return (
      <main className="container">
        <div className="card" style={{ margin: '4rem auto' }}>
          <p className="feedback" style={{ background: '#ffecec', color: '#a40f0f', borderColor: '#f2c6c6' }}>{error}</p>
        </div>
      </main>
    );
  }

  if (!profile) {
    return (
      <main className="container">
        <div className="card" style={{ margin: '4rem auto' }}>
          <p>Loading dashboard...</p>
        </div>
      </main>
    );
  }

  const color = getDeptColor(profile?.department);

  return (
    <main className="container">
      <div className="card dept-themed" style={{ margin: '4rem auto', maxWidth: 900, ['--dept-color' as any]: color } as React.CSSProperties}>
        <div className="page-header">
          <h1>Student Dashboard</h1>
          <p className="small-note">Welcome back, {profile.name}. Review your OJT details and continue your application process.</p>
        </div>
        <div className="dashboard-grid">
          <div className="metric-card">
            <h2>{profile.studentId}</h2>
            <p>Student ID</p>
          </div>
          <div className="metric-card">
            <h2>{profile.department}</h2>
            <p>Department</p>
          </div>
          <div className="metric-card">
            <h2>{profile.requiredOjtHours}</h2>
            <p>Required OJT Hours</p>
          </div>
        </div>
        <section style={{ marginTop: '1.5rem' }}>
          <div className="metric-card">
            <h2>Contact</h2>
            <p>{profile.email}</p>
          </div>
        </section>
        <button type="button" onClick={logout} className="primary" style={{ marginTop: '1.5rem' }}>
          Logout
        </button>
      </div>
    </main>
  );
}

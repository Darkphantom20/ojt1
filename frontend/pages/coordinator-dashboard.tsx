import { useEffect, useState } from 'react';
import { apiBase } from '../lib/api';
import { getDeptColor } from '../lib/departmentColors';

interface CoordinatorProfile {
  id: number;
  fullName: string;
  email: string;
  department: string;
}

export default function CoordinatorDashboard() {
  const [profile, setProfile] = useState<CoordinatorProfile | null>(null);
  const [error, setError] = useState('');

  useEffect(() => {
    async function fetchProfile() {
      const token = window.localStorage.getItem('ojt_coordinator_token');
      if (!token) {
        setError('Coordinator login required.');
        return;
      }

      try {
        const response = await fetch(`${apiBase}/api/coordinator/profile`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (!response.ok) {
          setError('Unable to load coordinator profile.');
          return;
        }
        setProfile(await response.json());
      } catch (err) {
        setError('Unable to connect to backend.');
      }
    }

    fetchProfile();
  }, []);

  function logout() {
    window.localStorage.removeItem('ojt_coordinator_token');
    window.location.href = '/coordinator-login';
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
          <p>Loading coordinator dashboard...</p>
        </div>
      </main>
    );
  }

  const color = getDeptColor(profile?.department);

  return (
    <main className="container">
      <div className="card dept-themed" style={{ margin: '4rem auto', maxWidth: 900, ['--dept-color' as any]: color } as React.CSSProperties}>
        <div className="page-header">
          <h1>Coordinator Dashboard</h1>
          <p className="small-note">Manage student assignments and track progress for your department.</p>
        </div>
        <div className="dashboard-grid">
          <div className="metric-card">
            <h2>{profile.fullName}</h2>
            <p>Coordinator</p>
          </div>
          <div className="metric-card">
            <h2>{profile.department}</h2>
            <p>Department</p>
          </div>
          <div className="metric-card">
            <h2>{profile.email}</h2>
            <p>Contact Email</p>
          </div>
        </div>
        <button type="button" onClick={logout} className="primary" style={{ marginTop: '1.5rem' }}>
          Logout
        </button>
      </div>
    </main>
  );
}

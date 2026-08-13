import { useEffect, useState } from 'react';
import { apiBase } from '../lib/api';
import { getDeptColor } from '../lib/departmentColors';

interface AdminDashboardData {
  totalStudents: number;
  totalCoordinators: number;
  pendingVerifications: number;
}

export default function AdminDashboard() {
  const [data, setData] = useState<AdminDashboardData | null>(null);
  const [error, setError] = useState('');

  useEffect(() => {
    async function fetchDashboard() {
      const token = window.localStorage.getItem('ojt_admin_token');
      if (!token) {
        setError('Admin login required.');
        return;
      }

      try {
        const response = await fetch(`${apiBase}/api/admin/dashboard`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (!response.ok) {
          setError('Unable to load admin dashboard.');
          return;
        }
        setData(await response.json());
      } catch (err) {
        setError('Unable to connect to backend.');
      }
    }

    fetchDashboard();
  }, []);

  function logout() {
    window.localStorage.removeItem('ojt_admin_token');
    window.location.href = '/admin-login';
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

  if (!data) {
    return (
      <main className="container">
        <div className="card" style={{ margin: '4rem auto' }}>
          <p>Loading admin dashboard...</p>
        </div>
      </main>
    );
  }

  const color = getDeptColor();

  return (
    <main className="container">
      <div className="card dept-themed" style={{ margin: '4rem auto', maxWidth: 900, ['--dept-color' as any]: color } as React.CSSProperties}>
        <div className="page-header">
          <h1>Admin Dashboard</h1>
          <p className="small-note">Overview of key OJT system metrics and pending actions.</p>
        </div>
        <div className="dashboard-grid">
          <div className="metric-card">
            <h2>{data.totalStudents}</h2>
            <p>Total Students</p>
          </div>
          <div className="metric-card">
            <h2>{data.totalCoordinators}</h2>
            <p>Total Coordinators</p>
          </div>
          <div className="metric-card">
            <h2>{data.pendingVerifications}</h2>
            <p>Pending Verifications</p>
          </div>
        </div>
        <button type="button" onClick={logout} className="primary" style={{ marginTop: '1.5rem' }}>
          Logout
        </button>
      </div>
    </main>
  );
}

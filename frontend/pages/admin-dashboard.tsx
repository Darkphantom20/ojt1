import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { apiBase } from '../lib/api';
import { useRequireAuth } from '../lib/useRequireAuth';
import { AUTH_TOKENS } from '../lib/auth';
import { dashboardStyles, sidebarGradients, colors, DashboardHeader, ImprovedSidebar, DashboardFooter, DashboardContentWrapper } from '../lib/dashboardStyles';

interface AdminDashboardData {
  totalStudents: number;
  totalCoordinators: number;
  pendingVerifications: number;
}

const mockStudents = [
  { id: 1, name: 'Juan Dela Cruz', studentId: 'S001', status: 'approved', department: 'IT', enrollmentDate: '2024-01-15' },
  { id: 2, name: 'Maria Santos', studentId: 'S002', status: 'approved', department: 'Engineering', enrollmentDate: '2024-02-01' },
  { id: 3, name: 'Pedro Reyes', studentId: 'S003', status: 'pending_verification', department: 'IT', enrollmentDate: '2024-08-10' },
];

const mockCoordinators = [
  { id: 1, name: 'Dr. Angela Torres', department: 'IT', email: 'angela.torres@company.com', status: 'active' },
  { id: 2, name: 'Engr. Ramon Santos', department: 'Engineering', email: 'ramon.santos@company.com', status: 'active' },
  { id: 3, name: 'Prof. Lisa Mendoza', department: 'Business', email: 'lisa.mendoza@company.com', status: 'inactive' },
];

export default function AdminDashboard() {
  const router = useRouter();
  const { isLoading: isAuthLoading, isAuthorized } = useRequireAuth('admin');
  const [data, setData] = useState<AdminDashboardData | null>(null);
  const [error, setError] = useState('');
  const [activeTab, setActiveTab] = useState('overview');

  useEffect(() => {
    if (!isAuthorized) return;
    async function fetchDashboard() {
      const token = localStorage.getItem(AUTH_TOKENS.ADMIN);
      if (!token) {
        setError('Admin login required.');
        return;
      }
      try {
        const response = await fetch(`${apiBase}/api/admin/dashboard`, { headers: { Authorization: `Bearer ${token}` } });
        if (!response.ok) {
          setError('Unable to load dashboard.');
          return;
        }
        setData(await response.json());
      } catch (err) {
        setError('Unable to connect to backend.');
      }
    }
    fetchDashboard();
  }, [isAuthorized]);

  function logout() {
    localStorage.removeItem(AUTH_TOKENS.ADMIN);
    window.location.href = '/admin-login';
  }

  if (isAuthLoading) return <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: '#f5f5f5' }}><p>Verifying authentication...</p></div>;
  if (!isAuthorized) return null;
  if (error) return <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: '#f5f5f5' }}><div style={{ background: 'white', padding: '2rem', borderRadius: '8px' }}><p style={{ color: '#a40f0f', marginBottom: '1.5rem' }}>{error}</p><button onClick={() => router.push('/admin-login')} style={dashboardStyles.buttonPrimary}>Return to Login</button></div></div>;
  if (!data) return <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: '#f5f5f5' }}><p>Loading admin dashboard...</p></div>;

  const NavButton = ({ tab, label, badge }: any) => (
    <button onClick={() => setActiveTab(tab)} style={dashboardStyles.navButton(activeTab === tab)}>
      {label} {badge && <span style={{ marginLeft: '0.5rem', background: '#ef4444', padding: '0.25rem 0.5rem', borderRadius: '12px', fontSize: '0.8rem' }}>{badge}</span>}
    </button>
  );

  const MetricCard = ({ label, value, subtext, color }: any) => (
    <div style={dashboardStyles.metricCard(color)}>
      <div style={dashboardStyles.cardLabel}>{label}</div>
      <div style={dashboardStyles.cardValue(color)}>{value}</div>
      <div style={dashboardStyles.cardSubtext}>{subtext}</div>
    </div>
  );

  const navItems = [
    {
      label: 'Management',
      items: [
        { id: 'overview', label: 'Dashboard', icon: '📊' },
        { id: 'students', label: 'Manage Students', icon: '👨‍🎓' },
        { id: 'coordinators', label: 'Coordinators', icon: '👥' },
        { id: 'reports', label: 'Reports', icon: '📈' },
      ]
    }
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', minHeight: '100vh' }}>
      <DashboardHeader userType="admin" userName="Administrator" userRole="Admin" />
      
      <div style={{ display: 'flex', flex: 1 }}>
        <ImprovedSidebar 
          userType="admin" 
          profile={{ name: 'Admin Portal', email: 'admin@ojthub.com' }} 
          activeTab={activeTab} 
          setActiveTab={setActiveTab} 
          logout={logout}
          navItems={navItems}
        />

        <DashboardContentWrapper>
        <div style={{ padding: '2rem', animation: 'fadeIn 0.3s ease-in' }}>
          {activeTab === 'overview' && (
            <>
              <div style={dashboardStyles.metricsGrid}>
                <MetricCard label="Total Students" value={data.totalStudents} subtext="Active enrollments" color={colors.primary} />
                <MetricCard label="Coordinators" value={data.totalCoordinators} subtext="Across departments" color={colors.success} />
                <MetricCard label="Pending Approvals" value={data.pendingVerifications} subtext="Awaiting verification" color={colors.warning} />
                <MetricCard label="Approval Rate" value="96%" subtext="Success ratio" color={colors.secondary} />
              </div>

              <div style={dashboardStyles.twoColumnGrid}>
                <div style={dashboardStyles.card}>
                  <h3 style={dashboardStyles.cardTitle}>System Status</h3>
                  <div style={{ fontSize: '0.95rem', color: '#666' }}>
                    {['Database: Connected', 'API Server: Running', 'Frontend: Operational'].map((status, i) => (
                      <div key={i} style={{ paddingBottom: '0.75rem', borderBottom: i < 2 ? '1px solid #e5e7eb' : 'none', paddingTop: i > 0 ? '0.75rem' : 0 }}>
                        <span style={{ display: 'inline-block', width: '10px', height: '10px', background: '#10b981', borderRadius: '50%', marginRight: '0.5rem' }}></span>
                        {status}
                      </div>
                    ))}
                  </div>
                </div>

                <div style={dashboardStyles.card}>
                  <h3 style={dashboardStyles.cardTitle}>Quick Actions</h3>
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
                    <button style={dashboardStyles.buttonSecondary('#667eea')}>➕ Add New Coordinator</button>
                    <button style={dashboardStyles.buttonSecondary('#0891b2')}>🔍 Verify Pending Registrations</button>
                    <button style={dashboardStyles.buttonSecondary('#10b981')}>📥 Export Reports</button>
                  </div>
                </div>
              </div>
            </>
          )}

          {activeTab === 'students' && (
            <div style={dashboardStyles.card}>
              <h3 style={dashboardStyles.cardTitle}>All Students</h3>
              <div style={{ overflowX: 'auto' }}>
                <table style={dashboardStyles.table}>
                  <thead>
                    <tr style={dashboardStyles.tableHead}>
                      {['Student ID', 'Name', 'Department', 'Status', 'Enrollment Date'].map((h) => (
                        <th key={h} style={dashboardStyles.tableHeader}>{h}</th>
                      ))}
                    </tr>
                  </thead>
                  <tbody>
                    {mockStudents.map((s) => (
                      <tr key={s.id} style={dashboardStyles.tableRow}>
                        <td style={dashboardStyles.tableCell}>{s.studentId}</td>
                        <td style={dashboardStyles.tableCell}>{s.name}</td>
                        <td style={dashboardStyles.tableCell}>{s.department}</td>
                        <td style={dashboardStyles.tableCell}><span style={s.status === 'approved' ? dashboardStyles.badgeSuccess : dashboardStyles.badgeWarning}>{s.status === 'approved' ? '✅ Approved' : '⏳ Pending'}</span></td>
                        <td style={dashboardStyles.tableCell}>{s.enrollmentDate}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {activeTab === 'coordinators' && (
            <div style={dashboardStyles.card}>
              <h3 style={dashboardStyles.cardTitle}>Coordinators</h3>
              <div style={{ overflowX: 'auto' }}>
                <table style={dashboardStyles.table}>
                  <thead>
                    <tr style={dashboardStyles.tableHead}>
                      {['Name', 'Department', 'Email', 'Status'].map((h) => (
                        <th key={h} style={dashboardStyles.tableHeader}>{h}</th>
                      ))}
                    </tr>
                  </thead>
                  <tbody>
                    {mockCoordinators.map((c) => (
                      <tr key={c.id} style={dashboardStyles.tableRow}>
                        <td style={dashboardStyles.tableCell}>{c.name}</td>
                        <td style={dashboardStyles.tableCell}>{c.department}</td>
                        <td style={dashboardStyles.tableCell}>{c.email}</td>
                        <td style={dashboardStyles.tableCell}><span style={c.status === 'active' ? dashboardStyles.badgeSuccess : dashboardStyles.badgeWarning}>{c.status === 'active' ? '✅ Active' : '❌ Inactive'}</span></td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {activeTab === 'reports' && (
            <div style={dashboardStyles.card}>
              <h3 style={dashboardStyles.cardTitle}>System Reports</h3>
              <div style={{ padding: '2rem', background: '#f9fafb', borderRadius: '6px', textAlign: 'center', color: '#999' }}>📊 Advanced reports coming soon</div>
            </div>
          )}
        </div>
        </DashboardContentWrapper>
      </div>
      
      <DashboardFooter />
    </div>
  );
}

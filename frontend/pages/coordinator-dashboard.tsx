import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { apiBase } from '../lib/api';
import { useRequireAuth } from '../lib/useRequireAuth';
import { AUTH_TOKENS } from '../lib/auth';
import { dashboardStyles, sidebarGradients, colors, DashboardHeader, ImprovedSidebar, DashboardFooter, DashboardContentWrapper } from '../lib/dashboardStyles';

interface CoordinatorProfile {
  id: number;
  fullName: string;
  email: string;
  department: string;
}

const mockStudents = [
  { id: 1, name: 'Juan Dela Cruz', studentId: 'S001', status: 'approved', hoursLogged: 120, hoursRequired: 480, deploymentSite: 'Tech Corp' },
  { id: 2, name: 'Maria Santos', studentId: 'S002', status: 'approved', hoursLogged: 85, hoursRequired: 480, deploymentSite: 'Innovation Labs' },
  { id: 3, name: 'Pedro Reyes', studentId: 'S003', status: 'pending_approval', hoursLogged: 0, hoursRequired: 480, deploymentSite: 'Pending' },
  { id: 4, name: 'Ana Garcia', studentId: 'S004', status: 'approved', hoursLogged: 240, hoursRequired: 480, deploymentSite: 'Digital Solutions' },
  { id: 5, name: 'Carlos Mendez', studentId: 'S005', status: 'approved', hoursLogged: 180, hoursRequired: 480, deploymentSite: 'Tech Corp' },
];

export default function CoordinatorDashboard() {
  const router = useRouter();
  const { isLoading: isAuthLoading, isAuthorized } = useRequireAuth('coordinator');
  const [profile, setProfile] = useState<CoordinatorProfile | null>(null);
  const [error, setError] = useState('');
  const [activeTab, setActiveTab] = useState('overview');

  useEffect(() => {
    if (!isAuthorized) return;
    async function fetchProfile() {
      const token = localStorage.getItem(AUTH_TOKENS.COORDINATOR);
      if (!token) {
        setError('Coordinator login required.');
        return;
      }
      try {
        const response = await fetch(`${apiBase}/api/coordinator/profile`, { headers: { Authorization: `Bearer ${token}` } });
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
  }, [isAuthorized]);

  function logout() {
    localStorage.removeItem(AUTH_TOKENS.COORDINATOR);
    window.location.href = '/coordinator-login';
  }

  if (isAuthLoading) return <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: '#f5f5f5' }}><p>Verifying authentication...</p></div>;
  if (!isAuthorized) return null;
  if (error) return <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: '#f5f5f5' }}><div style={{ background: 'white', padding: '2rem', borderRadius: '8px' }}><p style={{ color: '#a40f0f', marginBottom: '1.5rem' }}>{error}</p><button onClick={() => router.push('/coordinator-login')} style={dashboardStyles.buttonPrimary}>Return to Login</button></div></div>;
  if (!profile) return <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: '#f5f5f5' }}><p>Loading coordinator dashboard...</p></div>;

  const approvedCount = mockStudents.filter(s => s.status === 'approved').length;
  const pendingCount = mockStudents.filter(s => s.status === 'pending_approval').length;
  const totalHoursLogged = mockStudents.reduce((sum, s) => sum + s.hoursLogged, 0);

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
        { id: 'students', label: 'Manage Students', icon: '👥' },
        { id: 'approvals', label: 'Pending Approvals', icon: '✅', badge: pendingCount },
        { id: 'reports', label: 'Reports', icon: '📈' },
      ]
    }
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', minHeight: '100vh' }}>
      <DashboardHeader userType="coordinator" userName={profile.fullName} userRole={profile.department} />
      
      <div style={{ display: 'flex', flex: 1 }}>
        <ImprovedSidebar 
          userType="coordinator" 
          profile={profile} 
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
                <MetricCard label="Approved Students" value={approvedCount} subtext="" color={colors.info} />
                <MetricCard label="Pending Approvals" value={pendingCount} subtext="" color={colors.warning} />
                <MetricCard label="Total Hours Logged" value={totalHoursLogged} subtext="" color={colors.success} />
                <MetricCard label="Department" value={profile.department} subtext="" color={colors.secondary} />
              </div>

              <div style={dashboardStyles.card}>
                <h3 style={dashboardStyles.cardTitle}>Recent Activity</h3>
                <div style={{ fontSize: '0.95rem', color: '#666' }}>
                  <div style={{ paddingBottom: '0.75rem', borderBottom: '1px solid #e5e7eb' }}>✅ {approvedCount} students approved and active</div>
                  <div style={{ paddingTop: '0.75rem' }}>⏳ {pendingCount} student(s) awaiting registration approval</div>
                </div>
              </div>
            </>
          )}

          {activeTab === 'students' && (
            <div style={dashboardStyles.card}>
              <h3 style={dashboardStyles.cardTitle}>Assigned Students</h3>
              <div style={{ overflowX: 'auto' }}>
                <table style={dashboardStyles.table}>
                  <thead>
                    <tr style={dashboardStyles.tableHead}>
                      {['Student ID', 'Name', 'Status', 'Hours', 'Deployment Site'].map((h) => (
                        <th key={h} style={dashboardStyles.tableHeader}>{h}</th>
                      ))}
                    </tr>
                  </thead>
                  <tbody>
                    {mockStudents.map((s) => (
                      <tr key={s.id} style={dashboardStyles.tableRow}>
                        <td style={dashboardStyles.tableCell}>{s.studentId}</td>
                        <td style={dashboardStyles.tableCell}>{s.name}</td>
                        <td style={dashboardStyles.tableCell}><span style={s.status === 'approved' ? dashboardStyles.badgeSuccess : dashboardStyles.badgeWarning}>{s.status === 'approved' ? '✅ Approved' : '⏳ Pending'}</span></td>
                        <td style={dashboardStyles.tableCell}>{s.hoursLogged}/{s.hoursRequired}</td>
                        <td style={dashboardStyles.tableCell}>{s.deploymentSite}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {activeTab === 'approvals' && (
            <div style={dashboardStyles.card}>
              <h3 style={dashboardStyles.cardTitle}>Pending Student Registrations</h3>
              {mockStudents.filter(s => s.status === 'pending_approval').map((student) => (
                <div key={student.id} style={{ padding: '1rem', border: '1px solid #e5e7eb', borderRadius: '6px', marginBottom: '1rem' }}>
                  <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem', marginBottom: '1rem' }}>
                    <div>
                      <div style={dashboardStyles.cardLabel}>Student ID</div>
                      <div style={{ fontSize: '1rem', fontWeight: '600', color: '#333' }}>{student.studentId}</div>
                    </div>
                    <div>
                      <div style={dashboardStyles.cardLabel}>Name</div>
                      <div style={{ fontSize: '1rem', fontWeight: '600', color: '#333' }}>{student.name}</div>
                    </div>
                  </div>
                  <div style={{ display: 'flex', gap: '1rem' }}>
                    <button style={dashboardStyles.buttonSuccess}>✅ Approve</button>
                    <button style={dashboardStyles.buttonDanger}>❌ Reject</button>
                  </div>
                </div>
              ))}
            </div>
          )}

          {activeTab === 'reports' && (
            <div style={dashboardStyles.card}>
              <h3 style={dashboardStyles.cardTitle}>Department Reports</h3>
              <div style={{ padding: '2rem', background: '#f9fafb', borderRadius: '6px', textAlign: 'center', color: '#999' }}>📊 Reports feature coming soon</div>
            </div>
          )}
        </div>
        </DashboardContentWrapper>
      </div>
      
      <DashboardFooter />
    </div>
  );
}

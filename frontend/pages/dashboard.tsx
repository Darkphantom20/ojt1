import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { apiBase } from '../lib/api';
import { useRequireAuth } from '../lib/useRequireAuth';
import { AUTH_TOKENS } from '../lib/auth';
import { dashboardStyles, sidebarGradients, colors, DashboardHeader, ImprovedSidebar, DashboardFooter, DashboardContentWrapper } from '../lib/dashboardStyles';

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
  const router = useRouter();
  const { isLoading: isAuthLoading, isAuthorized } = useRequireAuth('student');
  const [profile, setProfile] = useState<StudentProfile | null>(null);
  const [error, setError] = useState('');
  const [hoursLogged, setHoursLogged] = useState(0);
  const [activeTab, setActiveTab] = useState('overview');

  useEffect(() => {
    if (!isAuthorized) return;
    async function fetchProfile() {
      const token = localStorage.getItem(AUTH_TOKENS.STUDENT);
      if (!token) {
        setError('Login required.');
        return;
      }
      try {
        const response = await fetch(`${apiBase}/api/auth/profile`, { headers: { Authorization: `Bearer ${token}` } });
        if (!response.ok) {
          setError('Unable to load profile. Please login again.');
          return;
        }
        setProfile(await response.json());
        setHoursLogged(0);
      } catch (err) {
        setError('Unable to connect to backend.');
      }
    }
    fetchProfile();
  }, [isAuthorized]);

  function logout() {
    localStorage.removeItem(AUTH_TOKENS.STUDENT);
    window.location.href = '/login';
  }

  if (isAuthLoading) return <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: '#f5f5f5' }}><p>Verifying authentication...</p></div>;
  if (!isAuthorized) return null;
  if (error) return <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: '#f5f5f5' }}><div style={{ background: 'white', padding: '2rem', borderRadius: '8px', maxWidth: '500px', textAlign: 'center' }}><p style={{ color: '#a40f0f', marginBottom: '1.5rem' }}>{error}</p><button onClick={() => router.push('/login')} style={dashboardStyles.buttonPrimary}>Return to Login</button></div></div>;
  if (!profile) return <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: '#f5f5f5' }}><p>Loading dashboard...</p></div>;

  const hoursRemaining = Math.max(0, profile.requiredOjtHours - hoursLogged);
  const progressPercent = (hoursLogged / profile.requiredOjtHours) * 100;

  const NavButton = ({ tab, label, icon }: { tab: string; label: string; icon: string }) => (
    <button onClick={() => setActiveTab(tab)} style={dashboardStyles.navButton(activeTab === tab)}>{icon} {label}</button>
  );

  const MetricCard = ({ icon, label, value, color, subtext }: any) => (
    <div style={dashboardStyles.metricCard(color)}>
      <div style={dashboardStyles.cardLabel}>{label}</div>
      <div style={dashboardStyles.cardValue(color)}>{value}</div>
      <div style={dashboardStyles.cardSubtext}>{subtext}</div>
    </div>
  );

  const navItems = [
    {
      label: 'Dashboard',
      items: [
        { id: 'overview', label: 'Overview', icon: '📊' },
        { id: 'logbook', label: 'OJT Logbook', icon: '📖' },
        { id: 'progress', label: 'Progress', icon: '📈' },
      ]
    }
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', minHeight: '100vh' }}>
      <DashboardHeader userType="student" userName={profile.name} userRole="Student" />
      
      <div style={{ display: 'flex', flex: 1 }}>
        <ImprovedSidebar 
          userType="student" 
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
                <MetricCard icon="💼" label="Required OJT Hours" value={profile.requiredOjtHours} color={colors.primary} subtext="Total hours required" />
                <MetricCard icon="✅" label="Hours Completed" value={hoursLogged} color={colors.success} subtext="Hours logged so far" />
                <MetricCard icon="⏳" label="Hours Remaining" value={hoursRemaining} color={colors.warning} subtext="Still needed" />
                <MetricCard icon="🏢" label="Department" value={profile.department} color={colors.primary} subtext="Your department" />
              </div>

              <div style={{ ...dashboardStyles.card, marginBottom: '2rem' }}>
                <h3 style={dashboardStyles.cardTitle}>📊 OJT Progress</h3>
                <div style={dashboardStyles.progressBar}>
                  <div style={dashboardStyles.progressFill('linear-gradient(90deg, #667eea 0%, #764ba2 100%)', progressPercent)}>
                    {progressPercent > 5 && `${Math.round(progressPercent)}%`}
                  </div>
                </div>
                <div style={{ fontSize: '0.9rem', color: '#666', marginTop: '0.75rem' }}>
                  {hoursLogged} of {profile.requiredOjtHours} hours completed
                </div>
              </div>

              <div style={dashboardStyles.card}>
                <h3 style={dashboardStyles.cardTitle}>📋 Student Information</h3>
                <div style={dashboardStyles.responsiveGrid(200)}>
                  <div>
                    <div style={dashboardStyles.cardLabel}>Student ID</div>
                    <div style={{ fontSize: '1rem', fontWeight: '600', color: '#333' }}>{profile.studentId}</div>
                  </div>
                  <div>
                    <div style={dashboardStyles.cardLabel}>Full Name</div>
                    <div style={{ fontSize: '1rem', fontWeight: '600', color: '#333' }}>{profile.name}</div>
                  </div>
                  <div>
                    <div style={dashboardStyles.cardLabel}>Email</div>
                    <div style={{ fontSize: '1rem', fontWeight: '600', color: '#333' }}>{profile.email}</div>
                  </div>
                </div>
              </div>
            </>
          )}

          {activeTab === 'logbook' && (
            <>
              <div style={{ ...dashboardStyles.card, marginBottom: '2rem' }}>
                <h3 style={dashboardStyles.cardTitle}>➕ Log New Activity</h3>
                <div style={dashboardStyles.responsiveGrid(200)}>
                  <input type="date" placeholder="Date" style={{ padding: '0.75rem', border: '1px solid #e5e7eb', borderRadius: '4px', fontSize: '0.95rem' }} />
                  <input type="number" placeholder="Hours" min="0" max="8" style={{ padding: '0.75rem', border: '1px solid #e5e7eb', borderRadius: '4px', fontSize: '0.95rem' }} />
                  <input type="text" placeholder="Activity description" style={{ padding: '0.75rem', border: '1px solid #e5e7eb', borderRadius: '4px', fontSize: '0.95rem', gridColumn: 'span 2' }} />
                  <button style={dashboardStyles.buttonPrimary}>✅ Submit Log</button>
                </div>
              </div>

              <div style={dashboardStyles.card}>
                <h3 style={dashboardStyles.cardTitle}>📖 Recent Logbook Entries</h3>
                <div style={{ overflowX: 'auto' }}>
                  <table style={dashboardStyles.table}>
                    <thead>
                      <tr style={dashboardStyles.tableHead}>
                        <th style={dashboardStyles.tableHeader}>Date</th>
                        <th style={dashboardStyles.tableHeader}>Hours</th>
                        <th style={dashboardStyles.tableHeader}>Activity</th>
                        <th style={dashboardStyles.tableHeader}>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      {[
                        { date: '2024-08-12', hours: 8, activity: 'Database development and testing', status: 'approved' },
                        { date: '2024-08-11', hours: 7, activity: 'API integration and bug fixes', status: 'approved' },
                        { date: '2024-08-10', hours: 6, activity: 'Frontend development and UI design', status: 'pending' }
                      ].map((entry, i) => (
                        <tr key={i} style={dashboardStyles.tableRow}>
                          <td style={dashboardStyles.tableCell}>{entry.date}</td>
                          <td style={dashboardStyles.tableCell}>{entry.hours} hrs</td>
                          <td style={dashboardStyles.tableCell}>{entry.activity}</td>
                          <td style={dashboardStyles.tableCell}>
                            <span style={entry.status === 'approved' ? dashboardStyles.badgeSuccess : dashboardStyles.badgeWarning}>
                              {entry.status === 'approved' ? '✅ Approved' : '⏳ Pending'}
                            </span>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </>
          )}

          {activeTab === 'progress' && (
            <>
              <div style={dashboardStyles.twoColumnGrid}>
                <div style={dashboardStyles.card}>
                  <h3 style={dashboardStyles.cardTitle}>📅 This Week</h3>
                  <div style={{ display: 'flex', justifyContent: 'space-around', alignItems: 'flex-end', height: '200px', gap: '0.5rem' }}>
                    {[8, 7, 6, 8, 0, 0, 0].map((hours, i) => (
                      <div key={i} style={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
                        <div style={{ width: '40px', height: `${Math.max(20, hours * 25)}px`, background: hours > 0 ? 'linear-gradient(180deg, #667eea 0%, #764ba2 100%)' : '#e5e7eb', borderRadius: '4px', marginBottom: '0.5rem' }} />
                        <div style={{ fontSize: '0.8rem', color: '#666' }}>{['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'][i]}</div>
                        <div style={{ fontSize: '0.75rem', color: '#999', marginTop: '0.25rem' }}>{hours}h</div>
                      </div>
                    ))}
                  </div>
                  <div style={{ marginTop: '1rem', fontSize: '0.9rem', color: '#666', textAlign: 'center' }}>Total: 29 hours this week</div>
                </div>

                <div style={dashboardStyles.card}>
                  <h3 style={dashboardStyles.cardTitle}>📊 Monthly Statistics</h3>
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
                    {[
                      { label: 'Average Daily Hours', value: '6.8 hrs', percent: 85, color: '#667eea' },
                      { label: 'Hours Logged This Month', value: '136 hrs', percent: 57, color: '#10b981' },
                      { label: 'Approval Rate', value: '98%', percent: 98, color: '#f59e0b' }
                    ].map((stat, i) => (
                      <div key={i}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '0.5rem', fontSize: '0.9rem' }}>
                          <span>{stat.label}</span>
                          <span style={{ fontWeight: '600', color: stat.color }}>{stat.value}</span>
                        </div>
                        <div style={dashboardStyles.progressBar}>
                          <div style={{ ...dashboardStyles.progressFill(stat.color, stat.percent) }} />
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              </div>

              <div style={dashboardStyles.card}>
                <h3 style={dashboardStyles.cardTitle}>🎯 Performance Summary</h3>
                <div style={dashboardStyles.responsiveGrid(150)}>
                  <div style={{ padding: '1rem', background: '#f0f9ff', borderRadius: '6px', borderLeft: '3px solid #667eea' }}>
                    <div style={dashboardStyles.cardLabel}>On Track Status</div>
                    <div style={{ fontSize: '1.3rem', fontWeight: '600', color: '#667eea', marginTop: '0.5rem' }}>✅ Yes</div>
                  </div>
                  <div style={{ padding: '1rem', background: '#f0fdf4', borderRadius: '6px', borderLeft: '3px solid #10b981' }}>
                    <div style={dashboardStyles.cardLabel}>Work Quality</div>
                    <div style={{ fontSize: '1.3rem', fontWeight: '600', color: '#10b981', marginTop: '0.5rem' }}>⭐ Excellent</div>
                  </div>
                  <div style={{ padding: '1rem', background: '#fef3c7', borderRadius: '6px', borderLeft: '3px solid #f59e0b' }}>
                    <div style={dashboardStyles.cardLabel}>Days Left</div>
                    <div style={{ fontSize: '1.3rem', fontWeight: '600', color: '#f59e0b', marginTop: '0.5rem' }}>📅 28 days</div>
                  </div>
                </div>
              </div>
            </>
          )}
        </div>
        </DashboardContentWrapper>
      </div>
      
      <DashboardFooter />
    </div>
  );
}

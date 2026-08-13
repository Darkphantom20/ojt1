// Shared dashboard styles and utilities
export const dashboardStyles = {
  // Layout
  container: {
    display: 'flex',
    minHeight: '100vh',
    background: 'linear-gradient(135deg, #e0f2fe 0%, #f8fafc 28%, #eef2ff 65%, #f5f3ff 100%)',
    color: '#0f172a',
  },
  main: {
    flex: 1,
    padding: '1.25rem 1.5rem 1rem',
  },

  // Sidebar
  sidebar: {
    width: '220px',
    color: 'white',
    padding: '0',
    boxShadow: '18px 0 28px rgba(15, 23, 42, 0.16)',
    borderRight: '1px solid rgba(255,255,255,0.12)',
    backdropFilter: 'blur(6px)',
  },
  sidebarSection: {
    padding: '0 1.25rem',
    marginBottom: '1.5rem',
  },
  sidebarTitle: {
    margin: '0.5rem 0',
    fontSize: '1.15rem',
    fontWeight: '700',
  } as React.CSSProperties,
  sidebarSubtitle: {
    fontSize: '0.8rem',
    opacity: 0.8,
  },
  sidebarLabel: {
    fontSize: '0.8rem',
    opacity: 0.9,
    marginBottom: '0.5rem',
  },

  // Navigation
  navContainer: {
    padding: '0 0.75rem',
  },
  navSection: {
    marginBottom: '1rem',
  },
  navSectionLabel: {
    fontSize: '0.68rem',
    textTransform: 'uppercase',
    opacity: 0.72,
    marginBottom: '0.6rem',
    fontWeight: '700',
    letterSpacing: '0.08rem',
  } as React.CSSProperties,
  navButton: (active: boolean) => ({
    display: 'flex',
    alignItems: 'center',
    gap: '0.65rem',
    width: '100%',
    textAlign: 'left',
    padding: '0.7rem 0.85rem',
    background: active ? 'linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0.08))' : 'rgba(255,255,255,0.02)',
    color: 'white',
    border: active ? '1px solid rgba(255,255,255,0.15)' : '1px solid transparent',
    borderRadius: '12px',
    cursor: 'pointer',
    fontSize: '0.9rem',
    marginBottom: '0.45rem',
    transition: 'all 0.25s ease',
    boxShadow: active ? '0 10px 20px rgba(15, 23, 42, 0.12), inset 0 1px 0 rgba(255,255,255,0.15)' : 'inset 0 1px 0 rgba(255,255,255,0.04)',
    transform: active ? 'translateX(2px)' : 'translateX(0)',
  } as React.CSSProperties),
  navDivider: {
    borderTop: '1px solid rgba(255,255,255,0.2)',
    paddingTop: '1rem',
  },

  // Header
  pageHeader: {
    marginBottom: '1.5rem',
  },
  pageTitle: {
    margin: '0 0 0.4rem 0',
    fontSize: '1.8rem',
    color: '#0f172a',
  } as React.CSSProperties,
  pageSubtitle: {
    margin: 0,
    color: '#475569',
    fontSize: '0.92rem',
  },

  // Cards
  card: {
    background: 'rgba(255,255,255,0.9)',
    padding: '1.25rem',
    borderRadius: '18px',
    boxShadow: '0 12px 30px rgba(15, 23, 42, 0.08)',
    border: '1px solid rgba(148, 163, 184, 0.15)',
  },
  metricCard: (borderColor: string) => ({
    background: 'rgba(255,255,255,0.9)',
    padding: '1.2rem 1.1rem',
    borderRadius: '18px',
    boxShadow: '0 12px 28px rgba(15, 23, 42, 0.08)',
    borderLeft: `4px solid ${borderColor}`,
    border: '1px solid rgba(148, 163, 184, 0.12)',
  } as React.CSSProperties),

  // Grid
  metricsGrid: {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(190px, 1fr))',
    gap: '1.1rem',
    marginBottom: '1.5rem',
  },
  twoColumnGrid: {
    display: 'grid',
    gridTemplateColumns: '1fr 1fr',
    gap: '1.2rem',
  },
  responsiveGrid: (minWidth = 150) => ({
    display: 'grid',
    gridTemplateColumns: `repeat(auto-fit, minmax(${minWidth}px, 1fr))`,
    gap: '0.8rem',
  } as React.CSSProperties),

  // Typography
  cardTitle: {
    margin: '0 0 0.85rem 0',
    fontSize: '1.05rem',
    color: '#0f172a',
  } as React.CSSProperties,
  cardLabel: {
    fontSize: '0.75rem',
    color: '#64748b',
    marginBottom: '0.45rem',
    textTransform: 'uppercase',
    letterSpacing: '0.04rem',
  },
  cardValue: (color: string) => ({
    fontSize: '2.1rem',
    fontWeight: '800',
    lineHeight: 1.2,
    color,
  } as React.CSSProperties),
  cardSubtext: {
    fontSize: '0.78rem',
    color: '#64748b',
    marginTop: '0.35rem',
  },
  tableHeader: {
    textAlign: 'left',
    padding: '0.7rem 0.75rem',
    fontWeight: '700',
    color: '#0f172a',
  } as React.CSSProperties,
  tableCell: {
    padding: '0.7rem 0.75rem',
    color: '#334155',
  },

  // Buttons
  buttonPrimary: {
    padding: '0.7rem 1rem',
    background: 'linear-gradient(135deg, #4f46e5 0%, #6366f1 100%)',
    color: 'white',
    border: 'none',
    borderRadius: '12px',
    cursor: 'pointer',
    fontSize: '0.88rem',
    fontWeight: '600',
    transition: 'all 0.2s ease',
    boxShadow: '0 8px 20px rgba(79, 70, 229, 0.2)',
  } as React.CSSProperties,
  buttonSuccess: {
    padding: '0.7rem 1rem',
    background: 'linear-gradient(135deg, #10b981 0%, #34d399 100%)',
    color: 'white',
    border: 'none',
    borderRadius: '12px',
    cursor: 'pointer',
    fontSize: '0.88rem',
    fontWeight: '600',
    boxShadow: '0 8px 20px rgba(16, 185, 129, 0.18)',
  } as React.CSSProperties,
  buttonDanger: {
    padding: '0.7rem 1rem',
    background: 'linear-gradient(135deg, #ef4444 0%, #f87171 100%)',
    color: 'white',
    border: 'none',
    borderRadius: '12px',
    cursor: 'pointer',
    fontSize: '0.88rem',
    fontWeight: '600',
    boxShadow: '0 8px 20px rgba(239, 68, 68, 0.18)',
  } as React.CSSProperties,
  buttonSecondary: (bgColor = '#0891b2') => ({
    padding: '0.7rem 1rem',
    background: `linear-gradient(135deg, ${bgColor} 0%, ${bgColor}cc 100%)`,
    color: 'white',
    border: 'none',
    borderRadius: '12px',
    cursor: 'pointer',
    textAlign: 'left' as const,
    boxShadow: '0 8px 20px rgba(14, 116, 144, 0.15)',
  } as React.CSSProperties),

  // Status badges
  badgeSuccess: {
    display: 'inline-block',
    padding: '0.28rem 0.7rem',
    borderRadius: '999px',
    fontSize: '0.76rem',
    background: '#d1fae5',
    color: '#065f46',
    fontWeight: '700',
  } as React.CSSProperties,
  badgeWarning: {
    display: 'inline-block',
    padding: '0.28rem 0.7rem',
    borderRadius: '999px',
    fontSize: '0.76rem',
    background: '#fef3c7',
    color: '#92400e',
    fontWeight: '700',
  } as React.CSSProperties,
  badgeInfo: {
    display: 'inline-block',
    padding: '0.28rem 0.7rem',
    borderRadius: '999px',
    fontSize: '0.76rem',
    background: '#dbeafe',
    color: '#1e40af',
    fontWeight: '700',
  } as React.CSSProperties,

  // Progress bar
  progressBar: {
    height: '28px',
    background: '#e2e8f0',
    borderRadius: '999px',
    overflow: 'hidden',
    boxShadow: 'inset 0 2px 6px rgba(15, 23, 42, 0.08)',
  },
  progressFill: (color: string, percent: number) => ({
    height: '100%',
    width: `${Math.min(percent, 100)}%`,
    background: color,
    transition: 'width 0.35s ease',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    color: 'white',
    fontSize: '0.8rem',
    fontWeight: '700',
  } as React.CSSProperties),

  // Tables
  table: {
    width: '100%',
    borderCollapse: 'collapse',
    fontSize: '0.88rem',
  } as React.CSSProperties,
  tableHead: {
    borderBottom: '2px solid #e2e8f0',
    background: '#f8fafc',
  },
  tableRow: {
    borderBottom: '1px solid #e2e8f0',
  },

  // Transitions
  fadeIn: {
    animation: 'fadeIn 0.3s ease-in',
  },
  slideUp: {
    animation: 'slideUp 0.3s ease-out',
  },
};

// Sidebar gradients
export const sidebarGradients = {
  student: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
  coordinator: 'linear-gradient(135deg, #0891b2 0%, #06b6d4 100%)',
  admin: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
};

// Color scheme
export const colors = {
  primary: '#667eea',
  success: '#10b981',
  warning: '#f59e0b',
  danger: '#ef4444',
  info: '#0891b2',
  purple: '#667eea',
  secondary: '#764ba2',
};

// Loading states
export const LoadingScreen = () => (
  <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: '#f5f5f5' }}>
    <p>Loading...</p>
  </div>
);

export const ErrorScreen = ({ error, onRetry }: { error: string; onRetry: () => void }) => (
  <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: '#f5f5f5' }}>
    <div style={{ background: 'white', padding: '2rem', borderRadius: '8px', maxWidth: '500px', textAlign: 'center' }}>
      <p style={{ color: '#a40f0f', marginBottom: '1.5rem' }}>{error}</p>
      <button onClick={onRetry} style={{ ...dashboardStyles.buttonPrimary }}>
        Try Again
      </button>
    </div>
  </div>
);

// Background wrapper for dashboard main content
export const DashboardContentWrapper = ({ children }: { children: React.ReactNode }) => (
  <div style={{
    flex: 1,
    position: 'relative',
    background: 'linear-gradient(135deg, rgba(15, 23, 42, 0.12), rgba(30, 41, 59, 0.1)), radial-gradient(circle at top left, rgba(59, 130, 246, 0.18), transparent 18%), radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.18), transparent 24%), linear-gradient(120deg, #eef7ff 0%, #f6f9ff 42%, #f8fafc 100%)',
    backgroundSize: 'cover',
    backgroundPosition: 'center',
    backgroundAttachment: 'fixed',
    overflowY: 'auto',
  }}>
    <div style={{
      position: 'relative',
      zIndex: 1,
      backgroundImage: 'linear-gradient(rgba(148, 163, 184, 0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(148, 163, 184, 0.05) 1px, transparent 1px)',
      backgroundSize: '24px 24px',
      minHeight: '100%',
    }}>
      {children}
    </div>
  </div>
);

// Shared Header Component
export const DashboardHeader = ({ userType, userName, userRole }: { userType: 'student' | 'admin' | 'coordinator'; userName: string; userRole: string }) => {
  const roleColors: Record<string, { bg: string; text: string }> = {
    student: { bg: '#f3e8ff', text: '#4f46e5' },
    admin: { bg: '#fef2f2', text: '#b91c1c' },
    coordinator: { bg: '#ecfeff', text: '#0f766e' },
  };
  const colors = roleColors[userType];

  return (
    <div style={{
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      padding: '1rem 1.5rem',
      background: 'rgba(255,255,255,0.72)',
      backdropFilter: 'blur(10px)',
      borderBottom: '1px solid rgba(148, 163, 184, 0.18)',
      boxShadow: '0 10px 24px rgba(15, 23, 42, 0.06)',
      marginBottom: '1.25rem',
    }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: '0.85rem' }}>
        <div style={{
          width: '42px',
          height: '42px',
          borderRadius: '12px',
          background: colors.bg,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: '1.3rem',
          boxShadow: '0 8px 18px rgba(15, 23, 42, 0.08)',
        }}>
          {userType === 'student' && '👤'}
          {userType === 'admin' && '👨‍💼'}
          {userType === 'coordinator' && '👨‍🏫'}
        </div>
        <div>
          <div style={{ fontSize: '0.78rem', color: '#64748b', letterSpacing: '0.04rem', textTransform: 'uppercase' }}>Welcome back</div>
          <h2 style={{ margin: '0.2rem 0 0 0', fontSize: '1.08rem', color: '#0f172a', fontWeight: '700' }}>{userName}</h2>
        </div>
      </div>
      <div style={{
        padding: '0.45rem 0.8rem',
        background: colors.bg,
        color: colors.text,
        borderRadius: '999px',
        fontSize: '0.72rem',
        fontWeight: '700',
        textTransform: 'uppercase',
        letterSpacing: '0.06rem',
        boxShadow: '0 8px 18px rgba(15, 23, 42, 0.08)',
      }}>
        {userRole}
      </div>
    </div>
  );
};

// Improved Sidebar Component
export const ImprovedSidebar = ({ userType, profile, activeTab, setActiveTab, logout, navItems }: any) => {
  const gradients = sidebarGradients;
  const gradient = gradients[userType as keyof typeof gradients];

  return (
    <aside style={{
      ...dashboardStyles.sidebar,
      background: gradient,
      display: 'flex',
      flexDirection: 'column',
      overflow: 'hidden',
    }}>
      <div style={{
        padding: '1.3rem 1rem 1rem',
        borderBottom: '1px solid rgba(255,255,255,0.16)',
        background: 'rgba(15, 23, 42, 0.08)',
      }}>
        <div style={{
          width: '52px',
          height: '52px',
          borderRadius: '16px',
          background: 'rgba(255,255,255,0.18)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: '1.7rem',
          marginBottom: '0.8rem',
          border: '1px solid rgba(255,255,255,0.2)',
          boxShadow: '0 10px 20px rgba(15, 23, 42, 0.12)',
        }}>
          {userType === 'student' && '🎓'}
          {userType === 'admin' && '🛡️'}
          {userType === 'coordinator' && '🎯'}
        </div>
        <h3 style={{ margin: '0.35rem 0 0.2rem', fontSize: '1rem', fontWeight: '700', color: 'white', lineHeight: 1.3 }}>{profile.name || profile.fullName}</h3>
        <p style={{ margin: 0, fontSize: '0.76rem', opacity: 0.8 }}>
          {userType === 'student' && profile.studentId}
          {userType !== 'student' && profile.email}
        </p>
      </div>

      <nav style={{ flex: 1, padding: '1rem 0.75rem', overflow: 'auto' }}>
        {navItems.map((item: any, index: number) => (
          <div key={index}>
            {item.label && (
              <div style={{
                fontSize: '0.68rem',
                textTransform: 'uppercase',
                opacity: 0.72,
                padding: '0.7rem 0.55rem 0.45rem',
                fontWeight: '700',
                letterSpacing: '0.08rem',
              }}>
                {item.label}
              </div>
            )}
            {item.items && item.items.map((navItem: any, i: number) => (
              <button
                key={i}
                onClick={() => setActiveTab(navItem.id)}
                style={{
                  width: '100%',
                  background: activeTab === navItem.id ? 'linear-gradient(135deg, rgba(255,255,255,0.17), rgba(255,255,255,0.08))' : 'rgba(255,255,255,0.02)',
                  border: activeTab === navItem.id ? '1px solid rgba(255,255,255,0.18)' : '1px solid transparent',
                  color: 'white',
                  padding: '0.68rem 0.8rem',
                  textAlign: 'left',
                  cursor: 'pointer',
                  fontSize: '0.88rem',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '0.7rem',
                  transition: 'all 0.25s ease',
                  borderRadius: '12px',
                  fontWeight: activeTab === navItem.id ? '700' : '500',
                  boxShadow: activeTab === navItem.id ? '0 8px 18px rgba(15, 23, 42, 0.12)' : 'none',
                  transform: activeTab === navItem.id ? 'translateX(2px)' : 'translateX(0)',
                }}
                onMouseEnter={(e) => {
                  if (activeTab !== navItem.id) {
                    (e.target as HTMLButtonElement).style.background = 'rgba(255,255,255,0.09)';
                    (e.target as HTMLButtonElement).style.transform = 'translateX(2px)';
                  }
                }}
                onMouseLeave={(e) => {
                  if (activeTab !== navItem.id) {
                    (e.target as HTMLButtonElement).style.background = 'rgba(255,255,255,0.02)';
                    (e.target as HTMLButtonElement).style.transform = 'translateX(0)';
                  }
                }}
              >
                <span style={{ fontSize: '1rem', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', width: '24px', height: '24px', borderRadius: '8px', background: 'rgba(255,255,255,0.12)' }}>{navItem.icon}</span>
                <span>{navItem.label}</span>
                {navItem.badge && (
                  <span style={{
                    marginLeft: 'auto',
                    background: '#ef4444',
                    color: 'white',
                    padding: '0.18rem 0.42rem',
                    borderRadius: '999px',
                    fontSize: '0.68rem',
                    fontWeight: '700',
                  }}>
                    {navItem.badge}
                  </span>
                )}
              </button>
            ))}
          </div>
        ))}
      </nav>

      <div style={{ padding: '0.8rem 0.9rem 1rem', borderTop: '1px solid rgba(255,255,255,0.16)' }}>
        <button
          onClick={logout}
          style={{
            width: '100%',
            background: 'rgba(255,255,255,0.12)',
            border: '1px solid rgba(255,255,255,0.22)',
            color: 'white',
            padding: '0.7rem 0.9rem',
            borderRadius: '12px',
            cursor: 'pointer',
            fontSize: '0.88rem',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            gap: '0.5rem',
            fontWeight: '600',
            transition: 'all 0.25s ease',
            boxShadow: '0 8px 20px rgba(15, 23, 42, 0.12)',
          }}
          onMouseEnter={(e) => {
            (e.target as HTMLButtonElement).style.background = 'rgba(255,255,255,0.2)';
            (e.target as HTMLButtonElement).style.transform = 'translateY(-1px)';
          }}
          onMouseLeave={(e) => {
            (e.target as HTMLButtonElement).style.background = 'rgba(255,255,255,0.12)';
            (e.target as HTMLButtonElement).style.transform = 'translateY(0)';
          }}
        >
          🚪 Logout
        </button>
      </div>
    </aside>
  );
};

// Shared Footer Component
export const DashboardFooter = () => (
  <footer style={{
    background: 'rgba(15, 23, 42, 0.94)',
    color: '#e2e8f0',
    padding: '1.25rem 1.5rem 1rem',
    marginTop: '2rem',
    borderTop: '1px solid rgba(148, 163, 184, 0.18)',
    boxShadow: '0 -10px 25px rgba(15, 23, 42, 0.08)',
  }}>
    <div style={{
      maxWidth: '900px',
      margin: '0 auto',
      display: 'grid',
      gridTemplateColumns: '1.4fr 1fr',
      gap: '1.25rem',
      marginBottom: '1rem',
    }}>
      <div>
        <h4 style={{ fontSize: '0.85rem', fontWeight: '700', marginBottom: '0.7rem', color: 'white', letterSpacing: '0.04rem' }}>📚 About OJT Hub</h4>
        <p style={{ fontSize: '0.8rem', lineHeight: '1.55', margin: 0, color: '#cbd5e1' }}>OJT Hub is your centralized platform for monitoring on-the-job training and student performance.</p>
      </div>
      <div>
        <h4 style={{ fontSize: '0.85rem', fontWeight: '700', marginBottom: '0.7rem', color: 'white', letterSpacing: '0.04rem' }}>📞 Support</h4>
        <ul style={{ listStyle: 'none', padding: 0, margin: 0, fontSize: '0.8rem', color: '#cbd5e1' }}>
          <li style={{ marginBottom: '0.45rem' }}>📧 support@ojthub.com</li>
          <li style={{ marginBottom: '0.45rem' }}>💬 Live Chat</li>
          <li style={{ marginBottom: '0.45rem' }}>📖 Documentation</li>
        </ul>
      </div>
    </div>
    <div style={{
      borderTop: '1px solid rgba(148, 163, 184, 0.18)',
      paddingTop: '0.9rem',
      textAlign: 'center',
      fontSize: '0.76rem',
      color: '#94a3b8',
    }}>
      <p style={{ margin: 0 }}>© 2024 OJT Hub. All rights reserved.</p>
      <p style={{ margin: '0.35rem 0 0 0' }}>Built with care for seamless OJT monitoring</p>
    </div>
  </footer>
);

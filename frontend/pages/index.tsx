import { useEffect } from 'react';
import { useRouter } from 'next/router';
import Link from 'next/link';

export default function Home() {
  const router = useRouter();

  useEffect(() => {
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key.toLowerCase() === 'q') {
        router.push('/admin-login');
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [router]);

  return (
    <main className="hero-shell">
      <div className="hero-card">
        <div className="page-header">
          <h1>OJT Monitoring System</h1>
          <p className="small-note">Choose the portal that matches your role and continue with the online OJT process.</p>
        </div>
        <div className="portal-grid portal-grid--two">
          <Link href="/login" className="portal-card portal-card--icon" aria-label="Student Portal">
            <div className="portal-card__body portal-card__body--center">
              <p className="portal-label">Student</p>
            </div>
            <div className="portal-card__icon student-icon" aria-hidden="true">
              <svg viewBox="0 0 96 96" xmlns="http://www.w3.org/2000/svg">
                <rect x="0" y="0" width="96" height="96" rx="24" fill="url(#studentGradient)" />
                <circle cx="48" cy="34" r="14" fill="#ffffff" fillOpacity="0.95" />
                <path d="M32 58C32 48.06 39.06 40 49 40h-2c9.94 0 17 8.06 17 18v8H32v-8Z" fill="#ffffff" fillOpacity="0.9" />
                <path d="M30 56c10-8 18-8 36 0v6c0 6.63-5.37 12-12 12H42c-6.63 0-12-5.37-12-12v-6Z" fill="#f8fafc" fillOpacity="0.7" />
                <path d="M44 50h8v4h-8z" fill="#2563eb" opacity="0.9" />
                <defs>
                  <linearGradient id="studentGradient" x1="0" y1="0" x2="96" y2="96" gradientUnits="userSpaceOnUse">
                    <stop stopColor="#1D4ED8" />
                    <stop offset="1" stopColor="#60A5FA" />
                  </linearGradient>
                </defs>
              </svg>
            </div>
          </Link>

          <Link href="/coordinator-login" className="portal-card portal-card--icon" aria-label="Coordinator Login">
            <div className="portal-card__body portal-card__body--center">
              <p className="portal-label">Coordinator</p>
            </div>
            <div className="portal-card__icon coordinator-icon" aria-hidden="true">
              <svg viewBox="0 0 96 96" xmlns="http://www.w3.org/2000/svg">
                <rect x="0" y="0" width="96" height="96" rx="24" fill="url(#coordinatorGradient)" />
                <circle cx="48" cy="30" r="12" fill="#ffffff" fillOpacity="0.95" />
                <path d="M32 58c0-10 8-18 16-18s16 8 16 18v10H32V58Z" fill="#ffffff" fillOpacity="0.9" />
                <path d="M34 66h28v10H34V66Z" fill="#e0f2fe" opacity="0.9" />
                <path d="M38 52h20v8H38z" fill="#1e3a8a" opacity="0.9" />
                <path d="M36 30h24v6H36z" fill="#e2e8f0" opacity="0.6" />
                <defs>
                  <linearGradient id="coordinatorGradient" x1="0" y1="0" x2="96" y2="96" gradientUnits="userSpaceOnUse">
                    <stop stopColor="#0891b2" />
                    <stop offset="1" stopColor="#22d3ee" />
                  </linearGradient>
                </defs>
              </svg>
            </div>
          </Link>
        </div>
        <p className="hidden-note">Press <strong>q</strong> on this page to open the hidden admin login.</p>
      </div>
    </main>
  );
}

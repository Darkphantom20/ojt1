/**
 * Authentication utilities for the OJT system
 */

export const AUTH_TOKENS = {
  STUDENT: 'ojt_token',
  ADMIN: 'ojt_admin_token',
  COORDINATOR: 'ojt_coordinator_token',
};

export const DASHBOARD_ROUTES = {
  STUDENT: '/dashboard',
  ADMIN: '/admin-dashboard',
  COORDINATOR: '/coordinator-dashboard',
};

export const LOGIN_ROUTES = {
  STUDENT: '/login',
  ADMIN: '/admin-login',
  COORDINATOR: '/coordinator-login',
};

/**
 * Check which type of user is logged in
 */
export function getLoggedInUserType(): 'student' | 'admin' | 'coordinator' | null {
  if (typeof window === 'undefined') return null;

  if (localStorage.getItem(AUTH_TOKENS.STUDENT)) return 'student';
  if (localStorage.getItem(AUTH_TOKENS.ADMIN)) return 'admin';
  if (localStorage.getItem(AUTH_TOKENS.COORDINATOR)) return 'coordinator';
  return null;
}

/**
 * Get the token for the current logged-in user
 */
export function getCurrentToken(): string | null {
  if (typeof window === 'undefined') return null;

  return (
    localStorage.getItem(AUTH_TOKENS.STUDENT) ||
    localStorage.getItem(AUTH_TOKENS.ADMIN) ||
    localStorage.getItem(AUTH_TOKENS.COORDINATOR)
  );
}

/**
 * Get the appropriate dashboard route for a user type
 */
export function getDashboardRoute(userType: 'student' | 'admin' | 'coordinator'): string {
  return DASHBOARD_ROUTES[userType as keyof typeof DASHBOARD_ROUTES];
}

/**
 * Logout the current user
 */
export function logout(): void {
  if (typeof window === 'undefined') return;

  Object.values(AUTH_TOKENS).forEach((token) => {
    localStorage.removeItem(token);
  });
  window.location.href = '/';
}

/**
 * Check if user is authenticated
 */
export function isAuthenticated(): boolean {
  return getLoggedInUserType() !== null;
}

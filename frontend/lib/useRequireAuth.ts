import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { getLoggedInUserType, AUTH_TOKENS, LOGIN_ROUTES } from './auth';

/**
 * Hook to protect pages that require authentication
 * @param requiredUserType - The type of user required ('student', 'admin', 'coordinator', or null for any authenticated user)
 */
export function useRequireAuth(requiredUserType?: 'student' | 'admin' | 'coordinator' | null) {
  const router = useRouter();
  const [isLoading, setIsLoading] = useState(true);
  const [isAuthorized, setIsAuthorized] = useState(false);

  useEffect(() => {
    // Check authentication status
    const userType = getLoggedInUserType();

    if (!userType) {
      // No user logged in, redirect to login
      router.push('/');
      return;
    }

    if (requiredUserType && userType !== requiredUserType) {
      // Wrong user type, redirect to their dashboard
      const dashboardRoutes: Record<string, string> = {
        student: '/dashboard',
        admin: '/admin-dashboard',
        coordinator: '/coordinator-dashboard',
      };
      router.push(dashboardRoutes[userType] || '/');
      return;
    }

    setIsAuthorized(true);
    setIsLoading(false);
  }, [router, requiredUserType]);

  return { isLoading, isAuthorized };
}

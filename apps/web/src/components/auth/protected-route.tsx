import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '@/contexts/auth-context';
import { getAccessToken } from '@/lib/api';

export function ProtectedRoute() {
  const { isAuthenticated, isLoading } = useAuth();

  // While checking auth, only block if no token at all (avoid flash)
  if (isLoading && !getAccessToken()) {
    return <Navigate to="/login" replace />;
  }

  if (!isLoading && !isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  return <Outlet />;
}

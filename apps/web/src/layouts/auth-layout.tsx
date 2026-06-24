import { Outlet, Navigate } from 'react-router-dom';
import { useAuth } from '@/contexts/auth-context';
import { getAccessToken } from '@/lib/api';

function BrandPanel() {
  return (
    <div className="relative hidden lg:flex flex-col justify-between h-full bg-foreground text-background px-12 py-10 overflow-hidden">
      {/* Decorative rings */}
      <div className="pointer-events-none absolute -bottom-32 -left-32 size-[500px] rounded-full border border-background/10" />
      <div className="pointer-events-none absolute -bottom-16 -left-16 size-[360px] rounded-full border border-background/10" />

      <div className="relative">
        <span className="text-xl font-semibold tracking-tight">Keel</span>
      </div>

      <div className="relative flex flex-col gap-6">
        <blockquote className="flex flex-col gap-3">
          <p className="text-2xl font-medium leading-snug tracking-tight">
            "Finally a finance app that actually keeps up with how Nigerians manage money."
          </p>
          <footer className="text-sm text-background/60">
            Adaeze O. — Product designer, Lagos
          </footer>
        </blockquote>
        <p className="text-sm text-background/50 leading-relaxed max-w-xs">
          Track spending, set goals, and build wealth — built for the Nigerian professional.
        </p>
      </div>
    </div>
  );
}

export function AuthLayout() {
  const { isAuthenticated, isLoading } = useAuth();

  if (!isLoading && isAuthenticated) {
    return <Navigate to="/dashboard" replace />;
  }

  if (isLoading && getAccessToken()) {
    return null;
  }

  return (
    <div className="min-h-screen grid lg:grid-cols-2">
      <BrandPanel />
      <div className="flex items-center justify-center px-6 py-12 lg:px-12">
        <div className="w-full max-w-sm">
          <Outlet />
        </div>
      </div>
    </div>
  );
}

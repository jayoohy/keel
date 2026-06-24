import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { api, clearTokens, setTokens } from '@/lib/api';
import type { LoginDto, RegisterDto, AuthResponse, ApiResponse } from '@keel/types';

const AUTH_ME_KEY = ['auth', 'me'];

async function fetchMe() {
  const { data } = await api.get<ApiResponse<AuthResponse['user']>>('/auth/me');
  return data.data;
}

export function useMe() {
  return useQuery({
    queryKey: AUTH_ME_KEY,
    queryFn: fetchMe,
    retry: false,
    staleTime: 1000 * 60 * 5,
  });
}

export function useLogin() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (dto: LoginDto) => {
      const { data } = await api.post<ApiResponse<AuthResponse>>('/auth/login', dto);
      return data.data;
    },
    onSuccess: ({ accessToken, refreshToken, user }) => {
      setTokens(accessToken, refreshToken);
      qc.setQueryData(AUTH_ME_KEY, user);
    },
  });
}

export function useRegister() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (dto: RegisterDto) => {
      const { data } = await api.post<ApiResponse<AuthResponse>>('/auth/register', dto);
      return data.data;
    },
    onSuccess: ({ accessToken, refreshToken, user }) => {
      setTokens(accessToken, refreshToken);
      qc.setQueryData(AUTH_ME_KEY, user);
    },
  });
}

export function useLogout() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async () => {
      const refreshToken = localStorage.getItem('keel_refresh_token');
      if (refreshToken) await api.post('/auth/logout', { refreshToken }).catch(() => {});
    },
    onSettled: () => {
      clearTokens();
      qc.clear();
      window.location.href = '/login';
    },
  });
}

import api from '@/lib/axios'
import {
  type AuthTokenResponse,
  type User,
  clearTokens,
  setTokens,
} from '@/lib/auth'

export interface LoginPayload {
  email: string
  password: string
}

export interface RegisterPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export async function login(payload: LoginPayload): Promise<AuthTokenResponse> {
  const { data } = await api.post<AuthTokenResponse>('/login', payload)
  setTokens(data.access_token, data.refresh_token)
  return data
}

export async function register(payload: RegisterPayload): Promise<AuthTokenResponse> {
  const { data } = await api.post<AuthTokenResponse>('/register', payload)
  setTokens(data.access_token, data.refresh_token)
  return data
}

export async function logout(): Promise<void> {
  try {
    await api.post('/logout')
  } finally {
    clearTokens()
  }
}

export async function me(): Promise<User> {
  const { data } = await api.get<{ data: User }>('/me')
  return data.data
}

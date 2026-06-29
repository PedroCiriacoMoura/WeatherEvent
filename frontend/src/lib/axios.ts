import axios, {
  AxiosError,
  type AxiosInstance,
  type InternalAxiosRequestConfig,
} from 'axios'

import {
  type AuthTokenResponse,
  clearTokens,
  getAccessToken,
  getRefreshToken,
  setTokens,
} from './auth'

const baseURL = import.meta.env.VITE_API_URL ?? '/api'

const api: AxiosInstance = axios.create({
  baseURL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

const refreshClient = axios.create({ baseURL })

api.interceptors.request.use((config) => {
  const token = getAccessToken()
  if (token && !config.headers.Authorization) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

let refreshPromise: Promise<string> | null = null

async function refreshAccessToken(): Promise<string> {
  const refreshToken = getRefreshToken()
  if (!refreshToken) {
    throw new Error('No refresh token available')
  }

  const { data } = await refreshClient.post<AuthTokenResponse>('/refresh', null, {
    headers: { Authorization: `Bearer ${refreshToken}` },
  })

  setTokens(data.access_token, data.refresh_token)
  return data.access_token
}

type RetryableConfig = InternalAxiosRequestConfig & { _retry?: boolean }

api.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const original = error.config as RetryableConfig | undefined

    const canAttemptRefresh =
      error.response?.status === 401 &&
      original !== undefined &&
      !original._retry &&
      !original.url?.includes('/refresh') &&
      getRefreshToken() !== null

    if (!canAttemptRefresh || !original) {
      return Promise.reject(error)
    }

    original._retry = true

    try {
      const promise = (refreshPromise ??= refreshAccessToken().finally(() => {
        refreshPromise = null
      }))
      const newAccessToken = await promise

      original.headers.Authorization = `Bearer ${newAccessToken}`
      return api(original)
    } catch (refreshError) {
      clearTokens()
      return Promise.reject(refreshError)
    }
  },
)

export default api

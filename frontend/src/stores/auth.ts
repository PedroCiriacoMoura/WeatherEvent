import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

import { clearTokens, isAuthenticated as hasToken, type User } from '@/lib/auth'
import {
  login as loginRequest,
  logout as logoutRequest,
  me as meRequest,
  register as registerRequest,
  type LoginPayload,
  type RegisterPayload,
} from '@/services/auth'

export type AuthStatus = 'idle' | 'loading' | 'authenticated' | 'guest'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const status = ref<AuthStatus>('idle')
  const initialized = ref(false)

  const isAuthenticated = computed(() => hasToken() && user.value !== null)

  const initials = computed(() => {
    const name = user.value?.name?.trim()
    if (!name) return ''
    return name
      .split(/\s+/)
      .slice(0, 2)
      .map((part) => part.charAt(0).toUpperCase())
      .join('')
  })

  async function login(payload: LoginPayload): Promise<void> {
    const data = await loginRequest(payload)
    user.value = data.user
    status.value = 'authenticated'
  }

  async function register(payload: RegisterPayload): Promise<void> {
    const data = await registerRequest(payload)
    user.value = data.user
    status.value = 'authenticated'
  }

  async function fetchMe(): Promise<void> {
    status.value = 'loading'
    try {
      user.value = await meRequest()
      status.value = 'authenticated'
    } catch (error) {
      user.value = null
      status.value = 'guest'
      throw error
    }
  }

  async function logout(): Promise<void> {
    try {
      await logoutRequest()
    } finally {
      user.value = null
      status.value = 'guest'
    }
  }

  /** Bootstraps auth state on app start: hydrates the user from the token, if any. */
  async function init(): Promise<void> {
    if (initialized.value) return
    initialized.value = true

    if (!hasToken()) {
      status.value = 'guest'
      return
    }

    try {
      await fetchMe()
    } catch {
      clearTokens()
      status.value = 'guest'
    }
  }

  return {
    user,
    status,
    initialized,
    isAuthenticated,
    initials,
    login,
    register,
    fetchMe,
    logout,
    init,
  }
})

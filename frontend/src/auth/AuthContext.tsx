import { useCallback, useEffect, useMemo, useState } from 'react'
import { useQueryClient } from '@tanstack/react-query'
import { authApi, type LoginPayload } from '../api/authApi'
import { clearStoredAuth, loadStoredAuth, saveStoredAuth } from '../lib/authStorage'
import type { AuthResponse } from '../types'
import { AuthContext, type AuthContextValue } from './AuthContextBase'

interface AuthProviderProps {
  children: React.ReactNode
}

export function AuthProvider({ children }: AuthProviderProps) {
  const queryClient = useQueryClient()
  const [auth, setAuth] = useState(loadStoredAuth)

  useEffect(() => {
    function handleExpired() {
      setAuth(null)
      void queryClient.clear()
    }

    window.addEventListener('auth:expired', handleExpired)
    return () => window.removeEventListener('auth:expired', handleExpired)
  }, [queryClient])

  const applyAuth = useCallback((response: AuthResponse) => {
    const next = { token: response.token, user: response.user }
    saveStoredAuth(next)
    setAuth(next)
  }, [])

  const signIn = useCallback(async (payload: LoginPayload) => {
    const response = await authApi.login(payload)
    applyAuth(response)
    return response
  }, [applyAuth])

  const signOut = useCallback(() => {
    clearStoredAuth()
    setAuth(null)
    queryClient.clear()
  }, [queryClient])

  const value = useMemo<AuthContextValue>(
    () => ({
      token: auth?.token ?? null,
      user: auth?.user ?? null,
      isAuthenticated: Boolean(auth?.token),
      isOperator: auth?.user.role === 'operator' || auth?.user.role === 'admin',
      signIn,
      applyAuth,
      signOut,
    }),
    [applyAuth, auth, signIn, signOut],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

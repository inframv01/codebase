import { createContext } from 'react'
import type { LoginPayload } from '../api/authApi'
import type { AuthResponse, User } from '../types'

export interface AuthContextValue {
  token: string | null
  user: User | null
  isAuthenticated: boolean
  isOperator: boolean
  signIn: (payload: LoginPayload) => Promise<AuthResponse>
  applyAuth: (response: AuthResponse) => void
  signOut: () => void
}

export const AuthContext = createContext<AuthContextValue | null>(null)

import { api } from '../lib/api'
import type { ApiMessageResponse, AuthResponse } from '../types'

export interface RegisterPayload {
  name: string
  id_card_number: string
  atoll_id: number
  island_id: number
  house_name: string
  floor: string
  contact_numbers: string[]
  email: string
  password: string
}

export interface LoginPayload {
  email: string
  password: string
}

export interface ResetPasswordPayload {
  token: string
  email: string
  password: string
  password_confirmation: string
}

export const authApi = {
  async register(payload: RegisterPayload): Promise<ApiMessageResponse> {
    const { data } = await api.post<ApiMessageResponse>('/auth/register', payload)
    return data
  },

  async login(payload: LoginPayload): Promise<AuthResponse> {
    const { data } = await api.post<AuthResponse>('/auth/login', payload)
    return data
  },

  async googleRedirect(): Promise<{ url: string }> {
    const { data } = await api.get<{ url: string }>('/auth/google/redirect')
    return data
  },

  async googleCallback(query: string): Promise<AuthResponse> {
    const { data } = await api.get<AuthResponse>(`/auth/google/callback${query}`)
    return data
  },

  async forgotPassword(email: string): Promise<ApiMessageResponse> {
    const { data } = await api.post<ApiMessageResponse>('/auth/forgot-password', { email })
    return data
  },

  async resetPassword(payload: ResetPasswordPayload): Promise<ApiMessageResponse> {
    const { data } = await api.post<ApiMessageResponse>('/auth/reset-password', payload)
    return data
  },

  async resendOtp(email: string): Promise<ApiMessageResponse> {
    const { data } = await api.post<ApiMessageResponse>('/auth/otp/resend', { email })
    return data
  },

  async verifyOtp(email: string, code: string): Promise<AuthResponse> {
    const { data } = await api.post<AuthResponse>('/auth/otp/verify', { email, code })
    return data
  },
}

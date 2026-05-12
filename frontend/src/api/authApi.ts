import { api } from '../lib/api'
import { asMessage, asObject } from './responseGuards'
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
    const { data } = await api.post<unknown>('/auth/register', payload)
    return asMessage(data)
  },

  async login(payload: LoginPayload): Promise<AuthResponse> {
    const { data } = await api.post<unknown>('/auth/login', payload)
    return asObject<AuthResponse>(data)
  },

  async googleRedirect(): Promise<{ url: string }> {
    const { data } = await api.get<unknown>('/auth/google/redirect')
    return asObject<{ url: string }>(data)
  },

  async googleCallback(query: string): Promise<AuthResponse> {
    const { data } = await api.get<unknown>(`/auth/google/callback${query}`)
    return asObject<AuthResponse>(data)
  },

  async forgotPassword(email: string): Promise<ApiMessageResponse> {
    const { data } = await api.post<unknown>('/auth/forgot-password', { email })
    return asMessage(data)
  },

  async resetPassword(payload: ResetPasswordPayload): Promise<ApiMessageResponse> {
    const { data } = await api.post<unknown>('/auth/reset-password', payload)
    return asMessage(data)
  },

  async resendOtp(email: string): Promise<ApiMessageResponse> {
    const { data } = await api.post<unknown>('/auth/otp/resend', { email })
    return asMessage(data)
  },

  async verifyOtp(email: string, code: string): Promise<AuthResponse> {
    const { data } = await api.post<unknown>('/auth/otp/verify', { email, code })
    return asObject<AuthResponse>(data)
  },
}

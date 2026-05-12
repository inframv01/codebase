import { api } from '../lib/api'
import type { ApiMessageResponse, User } from '../types'

export interface UpdateMePayload {
  name?: string
  id_card_number?: string
  atoll_id?: number
  island_id?: number
  house_name?: string
  floor?: string
  email?: string
}

export const meApi = {
  async getMe(): Promise<User> {
    const { data } = await api.get<User>('/me')
    return data
  },

  async updateMe(payload: UpdateMePayload): Promise<User> {
    const { data } = await api.patch<User>('/me', payload)
    return data
  },

  async replaceContactNumbers(contactNumbers: string[]): Promise<ApiMessageResponse & { user: User }> {
    const { data } = await api.post<ApiMessageResponse & { user: User }>('/me/contact-numbers', {
      contact_numbers: contactNumbers,
    })
    return data
  },
}

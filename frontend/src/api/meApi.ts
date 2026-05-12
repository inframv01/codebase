import { api } from '../lib/api'
import { asMessage, asObject } from './responseGuards'
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
    const { data } = await api.get<unknown>('/me')
    return asObject<User>(data)
  },

  async updateMe(payload: UpdateMePayload): Promise<User> {
    const { data } = await api.patch<unknown>('/me', payload)
    return asObject<User>(data)
  },

  async replaceContactNumbers(contactNumbers: string[]): Promise<ApiMessageResponse & { user: User }> {
    const { data } = await api.post<unknown>('/me/contact-numbers', {
      contact_numbers: contactNumbers,
    })
    const response = asObject<ApiMessageResponse & { user?: User }>(data)

    return {
      ...asMessage(response),
      user: asObject<User>(response.user),
    }
  },
}

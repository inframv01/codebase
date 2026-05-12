import { api } from '../lib/api'
import { asArray, asObject } from './responseGuards'
import type { AddressPurpose, SavedAddress } from '../types'

export interface SavedAddressPayload {
  label: string
  purpose: AddressPurpose
  address: Record<string, unknown>
  contact_name: string
  contact_phone: string
  is_default?: boolean
}

export const addressApi = {
  async listAddresses(): Promise<SavedAddress[]> {
    const { data } = await api.get<unknown>('/addresses')
    return asArray<SavedAddress>(data)
  },

  async createAddress(payload: SavedAddressPayload): Promise<SavedAddress> {
    const { data } = await api.post<unknown>('/addresses', payload)
    return asObject<SavedAddress>(data)
  },

  async updateAddress(id: number, payload: SavedAddressPayload): Promise<SavedAddress> {
    const { data } = await api.patch<unknown>(`/addresses/${id}`, payload)
    return asObject<SavedAddress>(data)
  },

  async deleteAddress(id: number): Promise<void> {
    await api.delete(`/addresses/${id}`)
  },
}

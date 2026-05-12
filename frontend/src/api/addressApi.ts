import { api } from '../lib/api'
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
    const { data } = await api.get<SavedAddress[]>('/addresses')
    return data
  },

  async createAddress(payload: SavedAddressPayload): Promise<SavedAddress> {
    const { data } = await api.post<SavedAddress>('/addresses', payload)
    return data
  },

  async updateAddress(id: number, payload: SavedAddressPayload): Promise<SavedAddress> {
    const { data } = await api.patch<SavedAddress>(`/addresses/${id}`, payload)
    return data
  },

  async deleteAddress(id: number): Promise<void> {
    await api.delete(`/addresses/${id}`)
  },
}

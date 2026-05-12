import { api } from '../lib/api'
import { asArray, asObject } from './responseGuards'
import type { Atoll, BoatSchedule, Island, QuotePreview, ServiceType, TransportProvider } from '../types'

export interface QuotePreviewPayload {
  type: ServiceType
  destination_island_id: number
  weight_kg: number
}

export const lookupApi = {
  async getAtolls(): Promise<Atoll[]> {
    const { data } = await api.get<unknown>('/lookups/atolls')
    return asArray<Atoll>(data)
  },

  async getIslands(atollId?: number): Promise<Island[]> {
    const { data } = await api.get<unknown>('/lookups/islands', {
      params: { atoll_id: atollId },
    })
    return asArray<Island>(data)
  },

  async getTransportProviders(islandId?: number): Promise<TransportProvider[]> {
    const { data } = await api.get<unknown>('/lookups/transport-providers', {
      params: { island_id: islandId },
    })
    return asArray<TransportProvider>(data)
  },

  async getBoatSchedules(params: { island_id?: number; from?: string; to?: string }): Promise<BoatSchedule[]> {
    const { data } = await api.get<unknown>('/lookups/boats/schedules', { params })
    return asArray<BoatSchedule>(data)
  },

  async previewQuote(payload: QuotePreviewPayload): Promise<QuotePreview> {
    const { data } = await api.post<unknown>('/quotes/preview', payload)
    return asObject<QuotePreview>(data)
  },
}

import { api } from '../lib/api'
import type { Atoll, BoatSchedule, Island, QuotePreview, ServiceType, TransportProvider } from '../types'

export interface QuotePreviewPayload {
  type: ServiceType
  destination_island_id: number
  weight_kg: number
}

export const lookupApi = {
  async getAtolls(): Promise<Atoll[]> {
    const { data } = await api.get<Atoll[]>('/lookups/atolls')
    return data
  },

  async getIslands(atollId?: number): Promise<Island[]> {
    const { data } = await api.get<Island[]>('/lookups/islands', { params: { atoll_id: atollId } })
    return data
  },

  async getTransportProviders(islandId?: number): Promise<TransportProvider[]> {
    const { data } = await api.get<TransportProvider[]>('/lookups/transport-providers', {
      params: { island_id: islandId },
    })
    return data
  },

  async getBoatSchedules(params: { island_id?: number; from?: string; to?: string }): Promise<BoatSchedule[]> {
    const { data } = await api.get<BoatSchedule[]>('/lookups/boats/schedules', { params })
    return data
  },

  async previewQuote(payload: QuotePreviewPayload): Promise<QuotePreview> {
    const { data } = await api.post<QuotePreview>('/quotes/preview', payload)
    return data
  },
}

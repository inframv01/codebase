import { api } from '../lib/api'
import type {
  ApiMessageResponse,
  Atoll,
  Boat,
  BoatSchedule,
  DeliveryRequest,
  DeliveryStage,
  Island,
  IslandGroup,
  PaginatedResponse,
  Payment,
  PricingRule,
  ServiceType,
  TransportProvider,
} from '../types'

export type OperatorResource = 'atolls' | 'islands' | 'island-groups' | 'transport-providers' | 'boats' | 'pricing-rules'
export type OperatorItem = Atoll | Island | IslandGroup | TransportProvider | Boat | PricingRule
export type OperatorPayload = Record<string, string | number | boolean | number[] | undefined>

export interface OperatorDeliveryFilters {
  status?: string
  page?: number
  per_page?: number
}

export interface PricingRulePayload {
  scope_type: 'island' | 'island_group'
  scope_id: number
  service_type: ServiceType
  fixed_cost_cents: number
  variable_rate_cents_per_kg: number
  min_charge_cents: number
  requires_inspection?: boolean
  active?: boolean
}

export const operatorApi = {
  async listResource<TItem extends OperatorItem>(resource: OperatorResource): Promise<TItem[]> {
    const { data } = await api.get<TItem[]>(`/operator/${resource}`)
    return data
  },

  async createResource<TItem extends OperatorItem>(resource: OperatorResource, payload: OperatorPayload): Promise<TItem> {
    const { data } = await api.post<TItem>(`/operator/${resource}`, payload)
    return data
  },

  async updateResource<TItem extends OperatorItem>(
    resource: OperatorResource,
    id: number,
    payload: OperatorPayload,
  ): Promise<TItem> {
    const { data } = await api.patch<TItem>(`/operator/${resource}/${id}`, payload)
    return 'data' in Object(data) ? (data as unknown as { data: TItem }).data : data
  },

  async deleteResource(resource: OperatorResource, id: number): Promise<void> {
    await api.delete(`/operator/${resource}/${id}`)
  },

  async listBoatSchedules(boatId: number): Promise<BoatSchedule[]> {
    const { data } = await api.get<BoatSchedule[]>(`/operator/boats/${boatId}/schedules`)
    return data
  },

  async createBoatSchedule(boatId: number, payload: OperatorPayload): Promise<BoatSchedule> {
    const { data } = await api.post<BoatSchedule>(`/operator/boats/${boatId}/schedules`, payload)
    return data
  },

  async updateBoatSchedule(boatId: number, scheduleId: number, payload: OperatorPayload): Promise<BoatSchedule> {
    const { data } = await api.patch<BoatSchedule>(`/operator/boats/${boatId}/schedules/${scheduleId}`, payload)
    return data
  },

  async deleteBoatSchedule(boatId: number, scheduleId: number): Promise<void> {
    await api.delete(`/operator/boats/${boatId}/schedules/${scheduleId}`)
  },

  async listOperatorDeliveryRequests(
    filters: OperatorDeliveryFilters,
  ): Promise<PaginatedResponse<DeliveryRequest>> {
    const { data } = await api.get<PaginatedResponse<DeliveryRequest>>('/operator/delivery-requests', {
      params: filters,
    })
    return data
  },

  async quoteRequest(
    uuid: string,
    payload: { variable_cost_cents: number; total_cost_cents: number; notes?: string },
  ): Promise<ApiMessageResponse & { delivery_request: DeliveryRequest }> {
    const { data } = await api.post<ApiMessageResponse & { delivery_request: DeliveryRequest }>(
      `/operator/delivery-requests/${uuid}/quote`,
      payload,
    )
    return data
  },

  async acceptRequest(uuid: string): Promise<ApiMessageResponse & { delivery_request: DeliveryRequest }> {
    const { data } = await api.post<ApiMessageResponse & { delivery_request: DeliveryRequest }>(
      `/operator/delivery-requests/${uuid}/accept`,
    )
    return data
  },

  async stageRequest(
    uuid: string,
    payload: { stage: DeliveryStage; notes?: string },
  ): Promise<ApiMessageResponse & { delivery_request: DeliveryRequest }> {
    const { data } = await api.post<ApiMessageResponse & { delivery_request: DeliveryRequest }>(
      `/operator/delivery-requests/${uuid}/stage`,
      payload,
    )
    return data
  },

  async verifyPayment(uuid: string, paymentUuid: string): Promise<ApiMessageResponse & { payment: Payment }> {
    const { data } = await api.post<ApiMessageResponse & { payment: Payment }>(
      `/operator/delivery-requests/${uuid}/payments/${paymentUuid}/verify`,
    )
    return data
  },

  async rejectPayment(
    uuid: string,
    paymentUuid: string,
    rejectionReason?: string,
  ): Promise<ApiMessageResponse & { payment: Payment }> {
    const { data } = await api.post<ApiMessageResponse & { payment: Payment }>(
      `/operator/delivery-requests/${uuid}/payments/${paymentUuid}/reject`,
      { rejection_reason: rejectionReason },
    )
    return data
  },
}

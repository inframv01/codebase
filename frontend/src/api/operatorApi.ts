import { api } from '../lib/api'
import { asArray, asMessage, asObject, asPaginated } from './responseGuards'
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
    const { data } = await api.get<unknown>(`/operator/${resource}`)
    return asArray<TItem>(data)
  },

  async createResource<TItem extends OperatorItem>(resource: OperatorResource, payload: OperatorPayload): Promise<TItem> {
    const { data } = await api.post<unknown>(`/operator/${resource}`, payload)
    return asObject<TItem>(data)
  },

  async updateResource<TItem extends OperatorItem>(
    resource: OperatorResource,
    id: number,
    payload: OperatorPayload,
  ): Promise<TItem> {
    const { data } = await api.patch<unknown>(`/operator/${resource}/${id}`, payload)
    return asObject<TItem>(data)
  },

  async deleteResource(resource: OperatorResource, id: number): Promise<void> {
    await api.delete(`/operator/${resource}/${id}`)
  },

  async listBoatSchedules(boatId: number): Promise<BoatSchedule[]> {
    const { data } = await api.get<unknown>(`/operator/boats/${boatId}/schedules`)
    return asArray<BoatSchedule>(data)
  },

  async createBoatSchedule(boatId: number, payload: OperatorPayload): Promise<BoatSchedule> {
    const { data } = await api.post<unknown>(`/operator/boats/${boatId}/schedules`, payload)
    return asObject<BoatSchedule>(data)
  },

  async updateBoatSchedule(boatId: number, scheduleId: number, payload: OperatorPayload): Promise<BoatSchedule> {
    const { data } = await api.patch<unknown>(`/operator/boats/${boatId}/schedules/${scheduleId}`, payload)
    return asObject<BoatSchedule>(data)
  },

  async deleteBoatSchedule(boatId: number, scheduleId: number): Promise<void> {
    await api.delete(`/operator/boats/${boatId}/schedules/${scheduleId}`)
  },

  async listOperatorDeliveryRequests(
    filters: OperatorDeliveryFilters,
  ): Promise<PaginatedResponse<DeliveryRequest>> {
    const { data } = await api.get<unknown>('/operator/delivery-requests', {
      params: filters,
    })
    return asPaginated<DeliveryRequest>(data)
  },

  async quoteRequest(
    uuid: string,
    payload: { variable_cost_cents: number; total_cost_cents: number; notes?: string },
  ): Promise<ApiMessageResponse & { delivery_request: DeliveryRequest }> {
    const { data } = await api.post<unknown>(
      `/operator/delivery-requests/${uuid}/quote`,
      payload,
    )
    const response = asObject<ApiMessageResponse & { delivery_request?: DeliveryRequest }>(data)

    return {
      ...asMessage(response),
      delivery_request: asObject<DeliveryRequest>(response.delivery_request),
    }
  },

  async acceptRequest(uuid: string): Promise<ApiMessageResponse & { delivery_request: DeliveryRequest }> {
    const { data } = await api.post<unknown>(
      `/operator/delivery-requests/${uuid}/accept`,
    )
    const response = asObject<ApiMessageResponse & { delivery_request?: DeliveryRequest }>(data)

    return {
      ...asMessage(response),
      delivery_request: asObject<DeliveryRequest>(response.delivery_request),
    }
  },

  async stageRequest(
    uuid: string,
    payload: { stage: DeliveryStage; notes?: string },
  ): Promise<ApiMessageResponse & { delivery_request: DeliveryRequest }> {
    const { data } = await api.post<unknown>(
      `/operator/delivery-requests/${uuid}/stage`,
      payload,
    )
    const response = asObject<ApiMessageResponse & { delivery_request?: DeliveryRequest }>(data)

    return {
      ...asMessage(response),
      delivery_request: asObject<DeliveryRequest>(response.delivery_request),
    }
  },

  async verifyPayment(uuid: string, paymentUuid: string): Promise<ApiMessageResponse & { payment: Payment }> {
    const { data } = await api.post<unknown>(
      `/operator/delivery-requests/${uuid}/payments/${paymentUuid}/verify`,
    )
    const response = asObject<ApiMessageResponse & { payment?: Payment }>(data)

    return {
      ...asMessage(response),
      payment: asObject<Payment>(response.payment),
    }
  },

  async rejectPayment(
    uuid: string,
    paymentUuid: string,
    rejectionReason?: string,
  ): Promise<ApiMessageResponse & { payment: Payment }> {
    const { data } = await api.post<unknown>(
      `/operator/delivery-requests/${uuid}/payments/${paymentUuid}/reject`,
      { rejection_reason: rejectionReason },
    )
    const response = asObject<ApiMessageResponse & { payment?: Payment }>(data)

    return {
      ...asMessage(response),
      payment: asObject<Payment>(response.payment),
    }
  },
}

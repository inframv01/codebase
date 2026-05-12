import { api } from '../lib/api'
import type { ApiMessageResponse, DeliveryRequest, Payment, ServiceType } from '../types'

export interface CreateDeliveryPayload {
  type: ServiceType
  destination_island_id: number
  weight_kg: number
  transport_provider_id?: number
  boat_schedule_id?: number
  notes?: string
  tracking_number?: string
  order_image?: FileList
  address_line1?: string
  contact_name?: string
  contact_phone?: string
  shop_address_line1?: string
  quote_copy?: FileList
  items?: string
}

function appendIfSet(form: FormData, key: string, value: string | number | boolean | undefined): void {
  if (value !== undefined && value !== '') {
    form.append(key, String(value))
  }
}

export function createDeliveryFormData(payload: CreateDeliveryPayload): FormData {
  const form = new FormData()

  appendIfSet(form, 'type', payload.type)
  appendIfSet(form, 'destination_island_id', payload.destination_island_id)
  appendIfSet(form, 'weight_kg', payload.weight_kg)
  appendIfSet(form, 'transport_provider_id', payload.transport_provider_id)
  appendIfSet(form, 'boat_schedule_id', payload.boat_schedule_id)
  appendIfSet(form, 'notes', payload.notes)
  appendIfSet(form, 'tracking_number', payload.tracking_number)

  if (payload.order_image?.[0]) {
    form.append('order_image', payload.order_image[0])
  }

  if (payload.address_line1) {
    form.append('address[line1]', payload.address_line1)
  }

  appendIfSet(form, 'contact_name', payload.contact_name)
  appendIfSet(form, 'contact_phone', payload.contact_phone)

  if (payload.shop_address_line1) {
    form.append('shop_address[line1]', payload.shop_address_line1)
  }

  if (payload.quote_copy?.[0]) {
    form.append('quote_copy', payload.quote_copy[0])
  }

  if (payload.items) {
    payload.items
      .split('\n')
      .map((item) => item.trim())
      .filter(Boolean)
      .forEach((item, index) => form.append(`items[${index}]`, item))
  }

  return form
}

export const deliveryApi = {
  async listDeliveryRequests(): Promise<DeliveryRequest[]> {
    const { data } = await api.get<DeliveryRequest[]>('/delivery-requests')
    return data
  },

  async createDeliveryRequest(payload: CreateDeliveryPayload): Promise<DeliveryRequest> {
    const { data } = await api.post<DeliveryRequest>('/delivery-requests', createDeliveryFormData(payload))
    return data
  },

  async getDeliveryRequest(uuid: string): Promise<DeliveryRequest> {
    const { data } = await api.get<DeliveryRequest>(`/delivery-requests/${uuid}`)
    return data
  },

  async cancelDeliveryRequest(uuid: string): Promise<ApiMessageResponse & { delivery_request: DeliveryRequest }> {
    const { data } = await api.post<ApiMessageResponse & { delivery_request: DeliveryRequest }>(
      `/delivery-requests/${uuid}/cancel`,
    )
    return data
  },

  async uploadPaymentSlip(uuid: string, amountCents: number, slip: File): Promise<ApiMessageResponse & { payment: Payment }> {
    const form = new FormData()
    form.append('amount_cents', String(amountCents))
    form.append('slip', slip)

    const { data } = await api.post<ApiMessageResponse & { payment: Payment }>(
      `/delivery-requests/${uuid}/payments`,
      form,
    )
    return data
  },
}

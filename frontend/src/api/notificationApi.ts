import { api } from '../lib/api'
import { asMessage, asPaginated } from './responseGuards'
import type { ApiMessageResponse, DatabaseNotification, PaginatedResponse } from '../types'

export const notificationApi = {
  async listNotificationsPaginated(page = 1, perPage = 15): Promise<PaginatedResponse<DatabaseNotification>> {
    const { data } = await api.get<unknown>('/notifications', {
      params: { page, per_page: perPage },
    })
    return asPaginated<DatabaseNotification>(data)
  },

  async markNotificationRead(notificationId: string): Promise<ApiMessageResponse> {
    const { data } = await api.post<unknown>(`/notifications/${notificationId}/read`)
    return asMessage(data)
  },

  async markAllNotificationsRead(): Promise<ApiMessageResponse> {
    const { data } = await api.post<unknown>('/notifications/read-all')
    return asMessage(data)
  },
}

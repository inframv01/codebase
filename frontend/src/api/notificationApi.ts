import { api } from '../lib/api'
import type { ApiMessageResponse, DatabaseNotification, PaginatedResponse } from '../types'

export const notificationApi = {
  async listNotificationsPaginated(page = 1, perPage = 15): Promise<PaginatedResponse<DatabaseNotification>> {
    const { data } = await api.get<PaginatedResponse<DatabaseNotification>>('/notifications', {
      params: { page, per_page: perPage },
    })
    return data
  },

  async markNotificationRead(notificationId: string): Promise<ApiMessageResponse> {
    const { data } = await api.post<ApiMessageResponse>(`/notifications/${notificationId}/read`)
    return data
  },

  async markAllNotificationsRead(): Promise<ApiMessageResponse> {
    const { data } = await api.post<ApiMessageResponse>('/notifications/read-all')
    return data
  },
}

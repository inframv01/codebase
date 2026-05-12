import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useState } from 'react'
import { notificationApi } from '../../api/notificationApi'
import { Button } from '../../components/Button'
import { Card } from '../../components/Card'
import { EmptyState } from '../../components/EmptyState'
import { LoadingState } from '../../components/LoadingState'
import { PageHeader } from '../../components/PageHeader'
import { Pagination } from '../../components/Pagination'
import { formatDateTime } from '../../lib/format'

export default function NotificationsPage() {
  const [page, setPage] = useState(1)
  const queryClient = useQueryClient()
  const notifications = useQuery({
    queryKey: ['notifications', page],
    queryFn: () => notificationApi.listNotificationsPaginated(page),
  })
  const markRead = useMutation({
    mutationFn: notificationApi.markNotificationRead,
    onSuccess: async () => queryClient.invalidateQueries({ queryKey: ['notifications'] }),
  })
  const markAll = useMutation({
    mutationFn: notificationApi.markAllNotificationsRead,
    onSuccess: async () => queryClient.invalidateQueries({ queryKey: ['notifications'] }),
  })

  return (
    <div>
      <PageHeader
        title="Notifications"
        description="Delivery updates and operator messages."
        action={<Button type="button" variant="secondary" loading={markAll.isPending} onClick={() => markAll.mutate()}>Mark all read</Button>}
      />
      {notifications.isLoading ? <LoadingState label="Loading notifications..." /> : null}
      {!notifications.isLoading && notifications.data?.data.length === 0 ? (
        <EmptyState title="No notifications" message="New delivery updates will appear here." />
      ) : null}
      <div className="grid gap-3">
        {notifications.data?.data.map((notification) => (
          <Card key={notification.id}>
            <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
              <div>
                <h2 className="font-semibold text-slate-950">{notification.type.split('\\').at(-1)}</h2>
                <p className="mt-1 text-sm text-slate-600">{formatDateTime(notification.created_at)}</p>
                <pre className="mt-3 overflow-auto rounded-lg bg-slate-50 p-3 text-xs text-slate-700">
                  {JSON.stringify(notification.data, null, 2)}
                </pre>
              </div>
              {!notification.read_at ? (
                <Button type="button" variant="secondary" loading={markRead.isPending} onClick={() => markRead.mutate(notification.id)}>
                  Mark read
                </Button>
              ) : null}
            </div>
          </Card>
        ))}
      </div>
      {notifications.data ? <div className="mt-4"><Pagination page={notifications.data.meta} onPageChange={setPage} /></div> : null}
    </div>
  )
}

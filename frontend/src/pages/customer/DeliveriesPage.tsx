import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { deliveryApi } from '../../api/deliveryApi'
import { Button } from '../../components/Button'
import { Card } from '../../components/Card'
import { EmptyState } from '../../components/EmptyState'
import { LoadingState } from '../../components/LoadingState'
import { PageHeader } from '../../components/PageHeader'
import { Status } from '../../components/Status'
import { formatMoney, humanize } from '../../lib/format'

export default function DeliveriesPage() {
  const deliveries = useQuery({ queryKey: ['deliveries'], queryFn: deliveryApi.listDeliveryRequests })

  return (
    <div>
      <PageHeader
        title="Deliveries"
        description="Review delivery requests, quotes, stages, and payment status."
        action={<Link className="inline-flex min-h-11 items-center rounded-lg bg-teal-700 px-4 text-sm font-semibold text-white" to="/deliveries/new">Create request</Link>}
      />
      {deliveries.isLoading ? <LoadingState label="Loading deliveries..." /> : null}
      {!deliveries.isLoading && deliveries.data?.length === 0 ? (
        <EmptyState title="No delivery requests" message="Start with a post office, Male address, or shop request." action={<Button type="button">Create request</Button>} />
      ) : null}
      <div className="grid gap-3">
        {deliveries.data?.map((delivery) => (
          <Link key={delivery.uuid} to={`/deliveries/${delivery.uuid}`} className="block">
            <Card>
              <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                  <div className="flex flex-wrap items-center gap-2">
                    <h2 className="font-semibold capitalize text-slate-950">{humanize(delivery.type)}</h2>
                    <Status value={delivery.status} />
                  </div>
                  <p className="mt-1 text-sm text-slate-600">
                    {delivery.destination_island?.name ?? 'Destination pending'} · {delivery.uuid}
                  </p>
                </div>
                <p className="text-lg font-bold text-slate-950">{formatMoney(delivery.total_cost_cents)}</p>
              </div>
            </Card>
          </Link>
        ))}
      </div>
    </div>
  )
}

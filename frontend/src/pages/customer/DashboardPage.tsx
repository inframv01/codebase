import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { deliveryApi } from '../../api/deliveryApi'
import { Card } from '../../components/Card'
import { EmptyState } from '../../components/EmptyState'
import { LoadingState } from '../../components/LoadingState'
import { PageHeader } from '../../components/PageHeader'
import { Status } from '../../components/Status'
import { formatMoney, humanize } from '../../lib/format'

export default function DashboardPage() {
  const deliveries = useQuery({ queryKey: ['deliveries'], queryFn: deliveryApi.listDeliveryRequests })
  const recent = deliveries.data?.slice(0, 3) ?? []

  return (
    <div>
      <PageHeader
        title="Dashboard"
        description="Track active delivery requests and payments."
        action={<Link className="inline-flex min-h-11 items-center rounded-lg bg-teal-700 px-4 text-sm font-semibold text-white" to="/deliveries/new">Create request</Link>}
      />
      {deliveries.isLoading ? <LoadingState label="Loading deliveries..." /> : null}
      {!deliveries.isLoading && recent.length === 0 ? (
        <EmptyState
          title="No deliveries yet"
          message="Create a request to start tracking delivery progress."
          action={<Link className="font-semibold text-teal-700" to="/deliveries/new">Create request</Link>}
        />
      ) : null}
      <div className="grid gap-4 md:grid-cols-3">
        {recent.map((delivery) => (
          <Link key={delivery.uuid} to={`/deliveries/${delivery.uuid}`} className="block">
            <Card className="h-full">
              <div className="flex items-start justify-between gap-3">
                <div>
                  <p className="text-sm font-semibold capitalize text-slate-950">{humanize(delivery.type)}</p>
                  <p className="mt-1 text-sm text-slate-600">{delivery.destination_island?.name ?? 'Destination pending'}</p>
                </div>
                <Status value={delivery.status} />
              </div>
              <p className="mt-4 text-xl font-bold text-slate-950">{formatMoney(delivery.total_cost_cents)}</p>
            </Card>
          </Link>
        ))}
      </div>
    </div>
  )
}

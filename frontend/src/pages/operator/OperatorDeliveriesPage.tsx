import { useQuery } from '@tanstack/react-query'
import { useState } from 'react'
import { Link } from 'react-router-dom'
import { operatorApi } from '../../api/operatorApi'
import { Card } from '../../components/Card'
import { Field, inputClassName } from '../../components/Field'
import { LoadingState } from '../../components/LoadingState'
import { PageHeader } from '../../components/PageHeader'
import { Pagination } from '../../components/Pagination'
import { Status } from '../../components/Status'
import { formatMoney, humanize } from '../../lib/format'

const statuses = ['', 'pending_quote', 'awaiting_payment', 'payment_uploaded', 'in_transit', 'delivered', 'cancelled']

export default function OperatorDeliveriesPage() {
  const [page, setPage] = useState(1)
  const [status, setStatus] = useState('')
  const deliveries = useQuery({
    queryKey: ['operator', 'deliveries', page, status],
    queryFn: () => operatorApi.listOperatorDeliveryRequests({ page, status: status || undefined }),
  })

  return (
    <div>
      <PageHeader title="Delivery queue" description="Review customer requests and advance operator workflow." />
      <Card className="mb-4">
        <Field label="Status filter">
          <select
            className={inputClassName}
            value={status}
            onChange={(event) => {
              setPage(1)
              setStatus(event.target.value)
            }}
          >
            {statuses.map((option) => (
              <option key={option || 'all'} value={option}>
                {option ? humanize(option) : 'All statuses'}
              </option>
            ))}
          </select>
        </Field>
      </Card>
      {deliveries.isLoading ? <LoadingState label="Loading operator queue..." /> : null}
      <div className="grid gap-3">
        {deliveries.data?.data.map((delivery) => (
          <Link key={delivery.uuid} to={`/operator/deliveries/${delivery.uuid}`} className="block">
            <Card>
              <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                  <div className="flex flex-wrap items-center gap-2">
                    <h2 className="font-semibold capitalize text-slate-950">{humanize(delivery.type)}</h2>
                    <Status value={delivery.status} />
                  </div>
                  <p className="mt-1 text-sm text-slate-600">{delivery.destination_island?.name ?? 'Destination pending'} · {delivery.uuid}</p>
                </div>
                <p className="text-lg font-bold text-slate-950">{formatMoney(delivery.total_cost_cents)}</p>
              </div>
            </Card>
          </Link>
        ))}
      </div>
      {deliveries.data ? <div className="mt-4"><Pagination page={deliveries.data.meta} onPageChange={setPage} /></div> : null}
    </div>
  )
}

import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useForm } from 'react-hook-form'
import { useParams } from 'react-router-dom'
import { z } from 'zod'
import { deliveryApi } from '../../api/deliveryApi'
import { Button } from '../../components/Button'
import { Card } from '../../components/Card'
import { Field, inputClassName } from '../../components/Field'
import { LoadingState } from '../../components/LoadingState'
import { PageHeader } from '../../components/PageHeader'
import { Status } from '../../components/Status'
import { getErrorMessage, getFieldError } from '../../lib/errors'
import { formatDateTime, formatMoney, humanize } from '../../lib/format'

const paymentSchema = z.object({
  amount_cents: z.coerce.number().min(0, 'Amount is required.'),
  slip: z.instanceof(FileList).refine((files) => files.length > 0, 'Upload a payment slip.'),
})

type PaymentInput = z.input<typeof paymentSchema>
type PaymentValues = z.output<typeof paymentSchema>

export default function DeliveryDetailPage() {
  const { uuid = '' } = useParams()
  const queryClient = useQueryClient()
  const delivery = useQuery({ queryKey: ['deliveries', uuid], queryFn: () => deliveryApi.getDeliveryRequest(uuid), enabled: Boolean(uuid) })
  const cancel = useMutation({
    mutationFn: () => deliveryApi.cancelDeliveryRequest(uuid),
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: ['deliveries'] })
      await queryClient.invalidateQueries({ queryKey: ['deliveries', uuid] })
    },
  })
  const form = useForm<PaymentInput, unknown, PaymentValues>({
    resolver: zodResolver(paymentSchema),
    values: { amount_cents: delivery.data?.total_cost_cents ?? 0, slip: new DataTransfer().files },
  })
  const upload = useMutation({
    mutationFn: (values: PaymentValues) => deliveryApi.uploadPaymentSlip(uuid, values.amount_cents, values.slip[0]),
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: ['deliveries', uuid] })
    },
  })

  if (delivery.isLoading) {
    return <LoadingState label="Loading delivery..." />
  }

  if (!delivery.data) {
    return <Card>Delivery request was not found.</Card>
  }

  return (
    <div>
      <PageHeader title="Delivery detail" description={delivery.data.uuid} />
      <div className="grid gap-4 lg:grid-cols-[1fr_320px]">
        <div className="grid gap-4">
          <Card>
            <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
              <div>
                <h2 className="text-xl font-bold capitalize text-slate-950">{humanize(delivery.data.type)}</h2>
                <p className="mt-1 text-sm text-slate-600">{delivery.data.destination_island?.name ?? 'Destination pending'}</p>
              </div>
              <Status value={delivery.data.status} />
            </div>
            <dl className="mt-5 grid gap-3 text-sm md:grid-cols-2">
              <Info label="Current stage" value={humanize(delivery.data.current_stage)} />
              <Info label="Quote" value={formatMoney(delivery.data.total_cost_cents)} />
              <Info label="Confirmed" value={formatDateTime(delivery.data.quote_confirmed_at)} />
              <Info label="Transport" value={delivery.data.transport_provider?.name ?? 'Not selected'} />
            </dl>
          </Card>
          <Card>
            <h2 className="font-semibold text-slate-950">Stage history</h2>
            <div className="mt-4 grid gap-3">
              {delivery.data.stage_events?.length ? (
                delivery.data.stage_events.map((event, index) => (
                  <div key={`${event.stage}-${index}`} className="rounded-lg bg-slate-50 p-3">
                    <p className="font-semibold capitalize text-slate-950">{humanize(event.stage)}</p>
                    <p className="text-sm text-slate-600">{event.notes ?? formatDateTime(event.created_at)}</p>
                  </div>
                ))
              ) : (
                <p className="text-sm text-slate-600">No stage updates yet.</p>
              )}
            </div>
          </Card>
        </div>
        <aside className="grid gap-4 self-start">
          <Card>
            <h2 className="font-semibold text-slate-950">Payments</h2>
            <div className="mt-4 grid gap-3">
              {delivery.data.payments?.map((payment) => (
                <div key={payment.uuid} className="rounded-lg border border-slate-200 p-3">
                  <div className="flex items-center justify-between gap-2">
                    <p className="font-semibold text-slate-950">{formatMoney(payment.amount_cents)}</p>
                    <Status value={payment.status} />
                  </div>
                  {payment.rejection_reason ? <p className="mt-2 text-sm text-rose-700">{payment.rejection_reason}</p> : null}
                </div>
              ))}
              {!delivery.data.payments?.length ? <p className="text-sm text-slate-600">No payments uploaded.</p> : null}
            </div>
          </Card>
          <Card>
            <h2 className="font-semibold text-slate-950">Upload payment slip</h2>
            <form className="mt-4 grid gap-3" onSubmit={form.handleSubmit((values) => upload.mutate(values))} noValidate>
              <Field label="Amount in cents" error={form.formState.errors.amount_cents?.message ?? getFieldError(upload.error, 'amount_cents')}>
                <input className={inputClassName} type="number" {...form.register('amount_cents')} />
              </Field>
              <Field label="Slip" error={form.formState.errors.slip?.message ?? getFieldError(upload.error, 'slip')}>
                <input className={inputClassName} type="file" accept="image/png,image/jpeg,application/pdf" {...form.register('slip')} />
              </Field>
              {upload.isError ? <p className="text-sm text-rose-700">{getErrorMessage(upload.error)}</p> : null}
              <Button type="submit" loading={upload.isPending}>
                Upload slip
              </Button>
            </form>
          </Card>
          <Button type="button" variant="danger" loading={cancel.isPending} onClick={() => cancel.mutate()}>
            Cancel request
          </Button>
          {cancel.isError ? <p className="text-sm text-rose-700">{getErrorMessage(cancel.error)}</p> : null}
        </aside>
      </div>
    </div>
  )
}

interface InfoProps {
  label: string
  value: string
}

function Info({ label, value }: InfoProps) {
  return (
    <div>
      <dt className="text-slate-500">{label}</dt>
      <dd className="mt-1 font-semibold text-slate-950">{value}</dd>
    </div>
  )
}

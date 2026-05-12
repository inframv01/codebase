import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useForm } from 'react-hook-form'
import { useParams } from 'react-router-dom'
import { z } from 'zod'
import { operatorApi } from '../../api/operatorApi'
import { Button } from '../../components/Button'
import { Card } from '../../components/Card'
import { Field, inputClassName } from '../../components/Field'
import { LoadingState } from '../../components/LoadingState'
import { PageHeader } from '../../components/PageHeader'
import { Status } from '../../components/Status'
import { getErrorMessage, getFieldError } from '../../lib/errors'
import { formatMoney, humanize } from '../../lib/format'
import type { DeliveryStage } from '../../types'

const quoteSchema = z.object({
  variable_cost_cents: z.coerce.number().min(0, 'Variable cost is required.'),
  total_cost_cents: z.coerce.number().min(0, 'Total cost is required.'),
  notes: z.string().optional(),
})

const stageSchema = z.object({
  stage: z.enum(['accepted_by_operator', 'picked_up', 'in_transit', 'arrived_at_island', 'out_for_delivery', 'delivered', 'cancelled']),
  notes: z.string().optional(),
})

type QuoteInput = z.input<typeof quoteSchema>
type QuoteValues = z.output<typeof quoteSchema>
type StageValues = z.infer<typeof stageSchema>

export default function OperatorDeliveryDetailPage() {
  const { uuid = '' } = useParams()
  const queryClient = useQueryClient()
  const deliveries = useQuery({
    queryKey: ['operator', 'deliveries', 'detail-source', uuid],
    queryFn: () => operatorApi.listOperatorDeliveryRequests({ per_page: 100 }),
  })
  const delivery = deliveries.data?.data.find((item) => item.uuid === uuid)
  const quoteForm = useForm<QuoteInput, unknown, QuoteValues>({ resolver: zodResolver(quoteSchema), defaultValues: { variable_cost_cents: 0, total_cost_cents: 0, notes: '' } })
  const stageForm = useForm<StageValues>({ resolver: zodResolver(stageSchema), defaultValues: { stage: 'accepted_by_operator', notes: '' } })
  const invalidate = async () => {
    await queryClient.invalidateQueries({ queryKey: ['operator', 'deliveries'] })
  }
  const quote = useMutation({ mutationFn: (values: QuoteValues) => operatorApi.quoteRequest(uuid, values), onSuccess: invalidate })
  const accept = useMutation({ mutationFn: () => operatorApi.acceptRequest(uuid), onSuccess: invalidate })
  const stage = useMutation({
    mutationFn: (values: StageValues) => operatorApi.stageRequest(uuid, { stage: values.stage as DeliveryStage, notes: values.notes }),
    onSuccess: invalidate,
  })
  const verify = useMutation({ mutationFn: (paymentUuid: string) => operatorApi.verifyPayment(uuid, paymentUuid), onSuccess: invalidate })
  const reject = useMutation({ mutationFn: (paymentUuid: string) => operatorApi.rejectPayment(uuid, paymentUuid, 'Rejected by operator.'), onSuccess: invalidate })

  if (deliveries.isLoading) {
    return <LoadingState label="Loading operator delivery..." />
  }

  if (!delivery) {
    return <Card>Delivery request was not found in the operator queue.</Card>
  }

  return (
    <div>
      <PageHeader title="Operator delivery detail" description={delivery.uuid} />
      <div className="grid gap-4 lg:grid-cols-[1fr_360px]">
        <div className="grid gap-4">
          <Card>
            <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
              <div>
                <h2 className="text-xl font-bold capitalize text-slate-950">{humanize(delivery.type)}</h2>
                <p className="mt-1 text-sm text-slate-600">{delivery.destination_island?.name ?? 'Destination pending'}</p>
              </div>
              <Status value={delivery.status} />
            </div>
            <dl className="mt-5 grid gap-3 text-sm md:grid-cols-2">
              <Info label="Current stage" value={humanize(delivery.current_stage)} />
              <Info label="Quote total" value={formatMoney(delivery.total_cost_cents)} />
              <Info label="Fixed cost" value={formatMoney(delivery.fixed_cost_cents)} />
              <Info label="Variable cost" value={formatMoney(delivery.variable_cost_cents)} />
            </dl>
          </Card>
          <Card>
            <h2 className="font-semibold text-slate-950">Payment decisions</h2>
            <div className="mt-4 grid gap-3">
              {delivery.payments?.map((payment) => (
                <div key={payment.uuid} className="rounded-lg border border-slate-200 p-3">
                  <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                      <p className="font-semibold text-slate-950">{formatMoney(payment.amount_cents)}</p>
                      <Status value={payment.status} />
                    </div>
                    <div className="flex gap-2">
                      <Button type="button" variant="secondary" loading={verify.isPending} onClick={() => verify.mutate(payment.uuid)}>Verify</Button>
                      <Button type="button" variant="danger" loading={reject.isPending} onClick={() => reject.mutate(payment.uuid)}>Reject</Button>
                    </div>
                  </div>
                </div>
              ))}
              {!delivery.payments?.length ? <p className="text-sm text-slate-600">No payments uploaded.</p> : null}
            </div>
          </Card>
        </div>
        <aside className="grid gap-4 self-start">
          <Button type="button" loading={accept.isPending} onClick={() => accept.mutate()}>Accept request</Button>
          {accept.isError ? <p className="text-sm text-rose-700">{getErrorMessage(accept.error)}</p> : null}
          <Card>
            <h2 className="font-semibold text-slate-950">Confirm quote</h2>
            <form className="mt-4 grid gap-3" onSubmit={quoteForm.handleSubmit((values) => quote.mutate(values))} noValidate>
              <Field label="Variable cost cents" error={quoteForm.formState.errors.variable_cost_cents?.message ?? getFieldError(quote.error, 'variable_cost_cents')}>
                <input className={inputClassName} type="number" {...quoteForm.register('variable_cost_cents')} />
              </Field>
              <Field label="Total cost cents" error={quoteForm.formState.errors.total_cost_cents?.message ?? getFieldError(quote.error, 'total_cost_cents')}>
                <input className={inputClassName} type="number" {...quoteForm.register('total_cost_cents')} />
              </Field>
              <Field label="Notes" error={getFieldError(quote.error, 'notes')}>
                <textarea className={inputClassName} rows={3} {...quoteForm.register('notes')} />
              </Field>
              {quote.isError ? <p className="text-sm text-rose-700">{getErrorMessage(quote.error)}</p> : null}
              <Button type="submit" loading={quote.isPending}>Save quote</Button>
            </form>
          </Card>
          <Card>
            <h2 className="font-semibold text-slate-950">Advance stage</h2>
            <form className="mt-4 grid gap-3" onSubmit={stageForm.handleSubmit((values) => stage.mutate(values))} noValidate>
              <Field label="Stage" error={stageForm.formState.errors.stage?.message ?? getFieldError(stage.error, 'stage')}>
                <select className={inputClassName} {...stageForm.register('stage')}>
                  {stageSchema.shape.stage.options.map((option) => (
                    <option key={option} value={option}>{humanize(option)}</option>
                  ))}
                </select>
              </Field>
              <Field label="Notes" error={getFieldError(stage.error, 'notes')}>
                <textarea className={inputClassName} rows={3} {...stageForm.register('notes')} />
              </Field>
              {stage.isError ? <p className="text-sm text-rose-700">{getErrorMessage(stage.error)}</p> : null}
              <Button type="submit" loading={stage.isPending}>Update stage</Button>
            </form>
          </Card>
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

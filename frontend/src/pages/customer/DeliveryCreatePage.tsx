import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useEffect } from 'react'
import { useForm, useWatch } from 'react-hook-form'
import { useNavigate } from 'react-router-dom'
import { z } from 'zod'
import { deliveryApi } from '../../api/deliveryApi'
import { lookupApi } from '../../api/lookupApi'
import { Button } from '../../components/Button'
import { Card } from '../../components/Card'
import { Field, inputClassName } from '../../components/Field'
import { PageHeader } from '../../components/PageHeader'
import { getErrorMessage, getFieldError } from '../../lib/errors'
import { formatMoney } from '../../lib/format'

const schema = z.object({
  type: z.enum(['post_office', 'male_address', 'shop']),
  destination_island_id: z.coerce.number().min(1, 'Choose a destination island.'),
  weight_kg: z.coerce.number().positive('Weight must be greater than zero.'),
  transport_provider_id: z.coerce.number().optional(),
  boat_schedule_id: z.coerce.number().optional(),
  notes: z.string().optional(),
  tracking_number: z.string().optional(),
  order_image: z.instanceof(FileList).optional(),
  address_line1: z.string().optional(),
  contact_name: z.string().optional(),
  contact_phone: z.string().optional(),
  shop_address_line1: z.string().optional(),
  quote_copy: z.instanceof(FileList).optional(),
  items: z.string().optional(),
})

type FormInput = z.input<typeof schema>
type FormValues = z.output<typeof schema>

export default function DeliveryCreatePage() {
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const form = useForm<FormInput, unknown, FormValues>({
    resolver: zodResolver(schema),
    defaultValues: {
      type: 'post_office',
      destination_island_id: 0,
      weight_kg: 1,
      notes: '',
    },
  })
  const type = useWatch({ control: form.control, name: 'type' }) as FormValues['type']
  const destinationIslandId = Number(useWatch({ control: form.control, name: 'destination_island_id' }) || 0)
  const weightKg = Number(useWatch({ control: form.control, name: 'weight_kg' }) || 0)
  const islands = useQuery({ queryKey: ['lookups', 'islands'], queryFn: () => lookupApi.getIslands() })
  const providers = useQuery({
    queryKey: ['lookups', 'transport-providers', destinationIslandId],
    queryFn: () => lookupApi.getTransportProviders(destinationIslandId || undefined),
    enabled: destinationIslandId > 0,
  })
  const schedules = useQuery({
    queryKey: ['lookups', 'boat-schedules', destinationIslandId],
    queryFn: () => lookupApi.getBoatSchedules({ island_id: destinationIslandId }),
    enabled: destinationIslandId > 0,
  })
  const quote = useMutation({
    mutationFn: () =>
      lookupApi.previewQuote({
        type,
        destination_island_id: destinationIslandId,
        weight_kg: Number(weightKg),
      }),
  })
  const { mutate: previewQuote } = quote
  const create = useMutation({
    mutationFn: deliveryApi.createDeliveryRequest,
    onSuccess: async (delivery) => {
      await queryClient.invalidateQueries({ queryKey: ['deliveries'] })
      navigate(`/deliveries/${delivery.uuid}`)
    },
  })

  useEffect(() => {
    if (destinationIslandId > 0 && weightKg > 0) {
      previewQuote()
    }
  }, [type, destinationIslandId, weightKg, previewQuote])

  return (
    <div>
      <PageHeader title="Create delivery request" description="Submit details for post office, Male address, or shop delivery." />
      <form className="grid gap-4" onSubmit={form.handleSubmit((values) => create.mutate(values))} noValidate>
        <Card className="grid gap-4">
          <Field label="Service type" error={form.formState.errors.type?.message ?? getFieldError(create.error, 'type')}>
            <select className={inputClassName} {...form.register('type')}>
              <option value="post_office">Post office</option>
              <option value="male_address">Male address</option>
              <option value="shop">Shop</option>
            </select>
          </Field>
          <div className="grid gap-4 md:grid-cols-2">
            <Field label="Destination island" error={form.formState.errors.destination_island_id?.message ?? getFieldError(create.error, 'destination_island_id')}>
              <select className={inputClassName} {...form.register('destination_island_id')}>
                <option value={0}>Choose island</option>
                {islands.data?.map((island) => (
                  <option key={island.id} value={island.id}>
                    {island.name}
                  </option>
                ))}
              </select>
            </Field>
            <Field label="Weight in kg" error={form.formState.errors.weight_kg?.message ?? getFieldError(create.error, 'weight_kg')}>
              <input className={inputClassName} type="number" min="0.1" step="0.1" {...form.register('weight_kg')} />
            </Field>
          </div>
          {quote.data ? (
            <div className="rounded-lg border border-teal-200 bg-teal-50 p-4 text-sm text-teal-900">
              Quote: <strong>{formatMoney(quote.data.total_cost_cents)}</strong>
              {quote.data.requires_inspection ? ' after operator inspection' : ''}
            </div>
          ) : null}
        </Card>
        <Card className="grid gap-4">
          <div className="grid gap-4 md:grid-cols-2">
            <Field label="Transport provider" error={getFieldError(create.error, 'transport_provider_id')}>
              <select className={inputClassName} {...form.register('transport_provider_id')}>
                <option value="">Optional</option>
                {providers.data?.map((provider) => (
                  <option key={provider.id} value={provider.id}>
                    {provider.name}
                  </option>
                ))}
              </select>
            </Field>
            <Field label="Boat schedule" error={getFieldError(create.error, 'boat_schedule_id')}>
              <select className={inputClassName} {...form.register('boat_schedule_id')}>
                <option value="">Optional</option>
                {schedules.data?.map((schedule) => (
                  <option key={schedule.id} value={schedule.id}>
                    {schedule.boat?.name ?? 'Boat'} · {schedule.destination_island?.name ?? 'Destination'}
                  </option>
                ))}
              </select>
            </Field>
          </div>
          {type === 'post_office' ? (
            <>
              <Field label="Tracking number" error={getFieldError(create.error, 'tracking_number')}>
                <input className={inputClassName} {...form.register('tracking_number')} />
              </Field>
              <Field label="Order image or PDF" error={getFieldError(create.error, 'order_image')}>
                <input className={inputClassName} type="file" accept="image/png,image/jpeg,application/pdf" {...form.register('order_image')} />
              </Field>
            </>
          ) : null}
          {type === 'male_address' ? (
            <>
              <Field label="Address line" error={getFieldError(create.error, 'address')}>
                <input className={inputClassName} {...form.register('address_line1')} />
              </Field>
              <ContactFields register={form.register} error={create.error} />
            </>
          ) : null}
          {type === 'shop' ? (
            <>
              <Field label="Shop address" error={getFieldError(create.error, 'shop_address')}>
                <input className={inputClassName} {...form.register('shop_address_line1')} />
              </Field>
              <ContactFields register={form.register} error={create.error} />
              <Field label="Quote copy" error={getFieldError(create.error, 'quote_copy')}>
                <input className={inputClassName} type="file" accept="image/png,image/jpeg,application/pdf" {...form.register('quote_copy')} />
              </Field>
              <Field label="Items" error={getFieldError(create.error, 'items')}>
                <textarea className={inputClassName} rows={4} placeholder="One item per line" {...form.register('items')} />
              </Field>
            </>
          ) : null}
          <Field label="Notes" error={getFieldError(create.error, 'notes')}>
            <textarea className={inputClassName} rows={3} {...form.register('notes')} />
          </Field>
        </Card>
        {create.isError ? <p className="text-sm text-rose-700">{getErrorMessage(create.error)}</p> : null}
        <Button type="submit" loading={create.isPending}>
          Submit request
        </Button>
      </form>
    </div>
  )
}

interface ContactFieldsProps {
  register: ReturnType<typeof useForm<FormInput, unknown, FormValues>>['register']
  error: unknown
}

function ContactFields({ register, error }: ContactFieldsProps) {
  return (
    <div className="grid gap-4 md:grid-cols-2">
      <Field label="Contact name" error={getFieldError(error, 'contact_name')}>
        <input className={inputClassName} {...register('contact_name')} />
      </Field>
      <Field label="Contact phone" error={getFieldError(error, 'contact_phone')}>
        <input className={inputClassName} {...register('contact_phone')} />
      </Field>
    </div>
  )
}

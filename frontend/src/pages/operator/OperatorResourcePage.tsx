import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useMemo } from 'react'
import { useForm } from 'react-hook-form'
import { Link, useParams } from 'react-router-dom'
import { z } from 'zod'
import { operatorApi, type OperatorPayload, type OperatorResource } from '../../api/operatorApi'
import { Button } from '../../components/Button'
import { Card } from '../../components/Card'
import { Field, inputClassName } from '../../components/Field'
import { LoadingState } from '../../components/LoadingState'
import { PageHeader } from '../../components/PageHeader'
import { Status } from '../../components/Status'
import { getErrorMessage, getFieldError } from '../../lib/errors'
import type { Atoll, Boat, Island, IslandGroup, PricingRule, TransportProvider } from '../../types'

const resourceLabels: Record<OperatorResource, string> = {
  atolls: 'Atolls',
  islands: 'Islands',
  'island-groups': 'Island groups',
  'transport-providers': 'Transport providers',
  boats: 'Boats',
  'pricing-rules': 'Pricing rules',
}

const schema = z.object({
  code: z.string().optional(),
  name: z.string().optional(),
  atoll_id: z.coerce.number().optional(),
  type: z.string().optional(),
  contact_name: z.string().optional(),
  contact_phone: z.string().optional(),
  active: z.boolean().optional(),
  transport_provider_id: z.coerce.number().optional(),
  capacity_kg: z.coerce.number().optional(),
  scope_type: z.string().optional(),
  scope_id: z.coerce.number().optional(),
  service_type: z.string().optional(),
  fixed_cost_cents: z.coerce.number().optional(),
  variable_rate_cents_per_kg: z.coerce.number().optional(),
  min_charge_cents: z.coerce.number().optional(),
  requires_inspection: z.boolean().optional(),
})

type FormInput = z.input<typeof schema>
type FormValues = z.output<typeof schema>

export default function OperatorResourcePage() {
  const resource = (useParams().resource ?? 'atolls') as OperatorResource
  const queryClient = useQueryClient()
  const form = useForm<FormInput, unknown, FormValues>({
    resolver: zodResolver(schema),
    defaultValues: { active: true, requires_inspection: false, type: 'boat', scope_type: 'island', service_type: 'post_office' },
  })
  const resources = useQuery({ queryKey: ['operator', resource], queryFn: () => operatorApi.listResource(resource), enabled: resource in resourceLabels })
  const atolls = useQuery({ queryKey: ['operator', 'atolls', 'options'], queryFn: () => operatorApi.listResource<Atoll>('atolls') })
  const providers = useQuery({
    queryKey: ['operator', 'transport-providers', 'options'],
    queryFn: () => operatorApi.listResource<TransportProvider>('transport-providers'),
  })
  const create = useMutation({
    mutationFn: (values: FormValues) => operatorApi.createResource(resource, toPayload(resource, values)),
    onSuccess: async () => {
      form.reset({ active: true, requires_inspection: false, type: 'boat', scope_type: 'island', service_type: 'post_office' })
      await queryClient.invalidateQueries({ queryKey: ['operator', resource] })
    },
  })
  const remove = useMutation({
    mutationFn: (id: number) => operatorApi.deleteResource(resource, id),
    onSuccess: async () => queryClient.invalidateQueries({ queryKey: ['operator', resource] }),
  })
  const title = resourceLabels[resource] ?? 'Resource'
  const fields = useMemo(() => getFields(resource), [resource])

  return (
    <div>
      <PageHeader title={title} description="Create and remove operator configuration records." />
      <div className="grid gap-4 lg:grid-cols-[1fr_360px]">
        <div className="grid gap-3 self-start">
          {resources.isLoading ? <LoadingState label={`Loading ${title.toLowerCase()}...`} /> : null}
          {resources.data?.map((item) => (
            <Card key={item.id}>
              <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                  <h2 className="font-semibold text-slate-950">{getTitle(item)}</h2>
                  <p className="mt-1 text-sm text-slate-600">{getSummary(item)}</p>
                  {'active' in item ? <div className="mt-2"><Status value={item.active ? 'active' : 'inactive'} /></div> : null}
                  {resource === 'boats' ? (
                    <Link className="mt-3 inline-flex text-sm font-semibold text-teal-700" to={`/operator/boats/${item.id}/schedules`}>
                      Manage schedules
                    </Link>
                  ) : null}
                </div>
                <Button type="button" variant="danger" loading={remove.isPending} onClick={() => remove.mutate(item.id)}>
                  Delete
                </Button>
              </div>
            </Card>
          ))}
        </div>
        <Card className="self-start">
          <h2 className="font-semibold text-slate-950">Add {title.toLowerCase()}</h2>
          <form className="mt-4 grid gap-4" onSubmit={form.handleSubmit((values) => create.mutate(values))} noValidate>
            {fields.includes('code') ? (
              <Field label="Code" error={getFieldError(create.error, 'code')}>
                <input className={inputClassName} {...form.register('code')} />
              </Field>
            ) : null}
            {fields.includes('name') ? (
              <Field label="Name" error={getFieldError(create.error, 'name')}>
                <input className={inputClassName} {...form.register('name')} />
              </Field>
            ) : null}
            {fields.includes('atoll_id') ? (
              <Field label="Atoll" error={getFieldError(create.error, 'atoll_id')}>
                <select className={inputClassName} {...form.register('atoll_id')}>
                  <option value="">Choose atoll</option>
                  {atolls.data?.map((atoll) => (
                    <option key={atoll.id} value={atoll.id}>{atoll.code} - {atoll.name}</option>
                  ))}
                </select>
              </Field>
            ) : null}
            {fields.includes('type') ? (
              <Field label="Type" error={getFieldError(create.error, 'type')}>
                <select className={inputClassName} {...form.register('type')}>
                  <option value="boat">Boat</option>
                  <option value="inter_island">Inter-island</option>
                  <option value="inter_atoll">Inter-atoll</option>
                </select>
              </Field>
            ) : null}
            {fields.includes('transport_provider_id') ? (
              <Field label="Transport provider" error={getFieldError(create.error, 'transport_provider_id')}>
                <select className={inputClassName} {...form.register('transport_provider_id')}>
                  <option value="">Choose provider</option>
                  {providers.data?.map((provider) => (
                    <option key={provider.id} value={provider.id}>{provider.name}</option>
                  ))}
                </select>
              </Field>
            ) : null}
            {fields.includes('capacity_kg') ? (
              <Field label="Capacity kg" error={getFieldError(create.error, 'capacity_kg')}>
                <input className={inputClassName} type="number" step="0.01" {...form.register('capacity_kg')} />
              </Field>
            ) : null}
            {fields.includes('contact_name') ? (
              <Field label="Contact name" error={getFieldError(create.error, 'contact_name')}>
                <input className={inputClassName} {...form.register('contact_name')} />
              </Field>
            ) : null}
            {fields.includes('contact_phone') ? (
              <Field label="Contact phone" error={getFieldError(create.error, 'contact_phone')}>
                <input className={inputClassName} {...form.register('contact_phone')} />
              </Field>
            ) : null}
            {resource === 'pricing-rules' ? <PricingFields form={form} error={create.error} /> : null}
            {fields.includes('active') ? (
              <label className="flex min-h-11 items-center gap-2 text-sm font-medium text-slate-800">
                <input type="checkbox" {...form.register('active')} />
                Active
              </label>
            ) : null}
            {create.isError ? <p className="text-sm text-rose-700">{getErrorMessage(create.error)}</p> : null}
            <Button type="submit" loading={create.isPending}>Create</Button>
          </form>
        </Card>
      </div>
    </div>
  )
}

function getFields(resource: OperatorResource): string[] {
  switch (resource) {
    case 'atolls':
      return ['code', 'name']
    case 'islands':
      return ['atoll_id', 'name']
    case 'island-groups':
      return ['name']
    case 'transport-providers':
      return ['name', 'type', 'contact_name', 'contact_phone', 'active']
    case 'boats':
      return ['transport_provider_id', 'name', 'capacity_kg', 'active']
    case 'pricing-rules':
      return ['active']
  }
}

function toPayload(resource: OperatorResource, values: FormValues): OperatorPayload {
  if (resource === 'pricing-rules') {
    return {
      scope_type: values.scope_type,
      scope_id: values.scope_id,
      service_type: values.service_type,
      fixed_cost_cents: values.fixed_cost_cents,
      variable_rate_cents_per_kg: values.variable_rate_cents_per_kg,
      min_charge_cents: values.min_charge_cents,
      requires_inspection: values.requires_inspection,
      active: values.active,
    }
  }

  return {
    code: values.code,
    name: values.name,
    atoll_id: values.atoll_id,
    type: values.type,
    contact_name: values.contact_name,
    contact_phone: values.contact_phone,
    active: values.active,
    transport_provider_id: values.transport_provider_id,
    capacity_kg: values.capacity_kg,
  }
}

function getTitle(item: Atoll | Island | IslandGroup | TransportProvider | Boat | PricingRule): string {
  if ('code' in item) {
    return `${item.code} - ${item.name}`
  }

  if ('service_type' in item) {
    return `${item.service_type.replace('_', ' ')} pricing`
  }

  return item.name
}

function getSummary(item: Atoll | Island | IslandGroup | TransportProvider | Boat | PricingRule): string {
  if ('atoll' in item && item.atoll) {
    return item.atoll.name
  }

  if ('transport_provider' in item && item.transport_provider) {
    return item.transport_provider.name
  }

  if ('service_type' in item) {
    return `${item.scope_type} ${item.scope_id} · fixed ${item.fixed_cost_cents} cents`
  }

  if ('type' in item) {
    return item.type
  }

  return `ID ${item.id}`
}

interface PricingFieldsProps {
  form: ReturnType<typeof useForm<FormInput, unknown, FormValues>>
  error: unknown
}

function PricingFields({ form, error }: PricingFieldsProps) {
  return (
    <>
      <Field label="Scope type" error={getFieldError(error, 'scope_type')}>
        <select className={inputClassName} {...form.register('scope_type')}>
          <option value="island">Island</option>
          <option value="island_group">Island group</option>
        </select>
      </Field>
      <Field label="Scope id" error={getFieldError(error, 'scope_id')}>
        <input className={inputClassName} type="number" {...form.register('scope_id')} />
      </Field>
      <Field label="Service type" error={getFieldError(error, 'service_type')}>
        <select className={inputClassName} {...form.register('service_type')}>
          <option value="post_office">Post office</option>
          <option value="male_address">Male address</option>
          <option value="shop">Shop</option>
        </select>
      </Field>
      <Field label="Fixed cost cents" error={getFieldError(error, 'fixed_cost_cents')}>
        <input className={inputClassName} type="number" {...form.register('fixed_cost_cents')} />
      </Field>
      <Field label="Variable rate cents per kg" error={getFieldError(error, 'variable_rate_cents_per_kg')}>
        <input className={inputClassName} type="number" {...form.register('variable_rate_cents_per_kg')} />
      </Field>
      <Field label="Minimum charge cents" error={getFieldError(error, 'min_charge_cents')}>
        <input className={inputClassName} type="number" {...form.register('min_charge_cents')} />
      </Field>
      <label className="flex min-h-11 items-center gap-2 text-sm font-medium text-slate-800">
        <input type="checkbox" {...form.register('requires_inspection')} />
        Requires inspection
      </label>
    </>
  )
}

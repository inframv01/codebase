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
import { formatDateTime } from '../../lib/format'
import type { Atoll, Island } from '../../types'

const schema = z.object({
  origin_island_id: z.coerce.number().min(1, 'Choose an origin island.'),
  destination_island_id: z.coerce.number().min(1, 'Choose a destination island.'),
  departs_at: z.string().min(1, 'Departure time is required.'),
  arrives_at: z.string().min(1, 'Arrival time is required.'),
  status: z.string(),
  capacity_remaining_kg: z.coerce.number().optional(),
})

type FormInput = z.input<typeof schema>
type FormValues = z.output<typeof schema>

export default function BoatSchedulesPage() {
  const boatId = Number(useParams().boatId)
  const queryClient = useQueryClient()
  const form = useForm<FormInput, unknown, FormValues>({
    resolver: zodResolver(schema),
    defaultValues: { origin_island_id: 0, destination_island_id: 0, departs_at: '', arrives_at: '', status: 'scheduled' },
  })
  const schedules = useQuery({ queryKey: ['operator', 'boats', boatId, 'schedules'], queryFn: () => operatorApi.listBoatSchedules(boatId), enabled: Number.isFinite(boatId) })
  const islands = useQuery({ queryKey: ['operator', 'islands', 'options'], queryFn: () => operatorApi.listResource<Island>('islands') })
  const create = useMutation({
    mutationFn: (values: FormValues) => operatorApi.createBoatSchedule(boatId, values),
    onSuccess: async () => {
      form.reset()
      await queryClient.invalidateQueries({ queryKey: ['operator', 'boats', boatId, 'schedules'] })
    },
  })
  const remove = useMutation({
    mutationFn: (scheduleId: number) => operatorApi.deleteBoatSchedule(boatId, scheduleId),
    onSuccess: async () => queryClient.invalidateQueries({ queryKey: ['operator', 'boats', boatId, 'schedules'] }),
  })

  return (
    <div>
      <PageHeader title="Boat schedules" description={`Manage schedules for boat ${boatId}.`} />
      <div className="grid gap-4 lg:grid-cols-[1fr_360px]">
        <div className="grid gap-3 self-start">
          {schedules.isLoading ? <LoadingState label="Loading schedules..." /> : null}
          {schedules.data?.map((schedule) => (
            <Card key={schedule.id}>
              <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                  <h2 className="font-semibold text-slate-950">
                    {schedule.origin_island?.name ?? 'Origin'} to {schedule.destination_island?.name ?? 'Destination'}
                  </h2>
                  <p className="mt-1 text-sm text-slate-600">
                    {formatDateTime(schedule.departs_at)} · remaining {schedule.capacity_remaining_kg ?? 'n/a'} kg
                  </p>
                  <div className="mt-2"><Status value={schedule.status} /></div>
                </div>
                <Button type="button" variant="danger" loading={remove.isPending} onClick={() => remove.mutate(schedule.id)}>Delete</Button>
              </div>
            </Card>
          ))}
        </div>
        <Card className="self-start">
          <h2 className="font-semibold text-slate-950">Add schedule</h2>
          <form className="mt-4 grid gap-4" onSubmit={form.handleSubmit((values) => create.mutate(values))} noValidate>
            <IslandField label="Origin island" name="origin_island_id" islands={islands.data} error={getFieldError(create.error, 'origin_island_id')} register={form.register} />
            <IslandField label="Destination island" name="destination_island_id" islands={islands.data} error={getFieldError(create.error, 'destination_island_id')} register={form.register} />
            <Field label="Departs at" error={form.formState.errors.departs_at?.message ?? getFieldError(create.error, 'departs_at')}>
              <input className={inputClassName} type="datetime-local" {...form.register('departs_at')} />
            </Field>
            <Field label="Arrives at" error={form.formState.errors.arrives_at?.message ?? getFieldError(create.error, 'arrives_at')}>
              <input className={inputClassName} type="datetime-local" {...form.register('arrives_at')} />
            </Field>
            <Field label="Status" error={getFieldError(create.error, 'status')}>
              <select className={inputClassName} {...form.register('status')}>
                <option value="scheduled">Scheduled</option>
                <option value="departed">Departed</option>
                <option value="arrived">Arrived</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </Field>
            <Field label="Capacity remaining kg" error={getFieldError(create.error, 'capacity_remaining_kg')}>
              <input className={inputClassName} type="number" step="0.01" {...form.register('capacity_remaining_kg')} />
            </Field>
            {create.isError ? <p className="text-sm text-rose-700">{getErrorMessage(create.error)}</p> : null}
            <Button type="submit" loading={create.isPending}>Create schedule</Button>
          </form>
        </Card>
      </div>
    </div>
  )
}

interface IslandFieldProps {
  label: string
  name: 'origin_island_id' | 'destination_island_id'
  islands?: Island[]
  error?: string
  register: ReturnType<typeof useForm<FormInput, unknown, FormValues>>['register']
}

function IslandField({ label, name, islands, error, register }: IslandFieldProps) {
  return (
    <Field label={label} error={error}>
      <select className={inputClassName} {...register(name)}>
        <option value={0}>Choose island</option>
        {islands?.map((island) => (
          <option key={island.id} value={island.id}>
            {island.name} {(island.atoll as Atoll | undefined)?.code ? `(${island.atoll?.code})` : ''}
          </option>
        ))}
      </select>
    </Field>
  )
}

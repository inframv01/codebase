import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useEffect } from 'react'
import { useForm, useWatch } from 'react-hook-form'
import { z } from 'zod'
import { lookupApi } from '../../api/lookupApi'
import { meApi } from '../../api/meApi'
import { Button } from '../../components/Button'
import { Card } from '../../components/Card'
import { Field, inputClassName } from '../../components/Field'
import { LoadingState } from '../../components/LoadingState'
import { PageHeader } from '../../components/PageHeader'
import { getErrorMessage, getFieldError } from '../../lib/errors'

const schema = z.object({
  name: z.string().min(1, 'Name is required.'),
  id_card_number: z.string().min(1, 'ID card number is required.'),
  email: z.string().email('Enter a valid email address.'),
  atoll_id: z.coerce.number().min(1, 'Choose an atoll.'),
  island_id: z.coerce.number().min(1, 'Choose an island.'),
  house_name: z.string().min(1, 'House name is required.'),
  floor: z.string().min(1, 'Floor is required.'),
  contact_number_1: z.string().min(1, 'Primary contact number is required.'),
  contact_number_2: z.string().optional(),
  contact_number_3: z.string().optional(),
})

type FormInput = z.input<typeof schema>
type FormValues = z.output<typeof schema>

export default function ProfilePage() {
  const queryClient = useQueryClient()
  const me = useQuery({ queryKey: ['me'], queryFn: meApi.getMe })
  const form = useForm<FormInput, unknown, FormValues>({ resolver: zodResolver(schema) })
  const atollId = Number(useWatch({ control: form.control, name: 'atoll_id' }) || 0)
  const atolls = useQuery({ queryKey: ['lookups', 'atolls'], queryFn: lookupApi.getAtolls })
  const islands = useQuery({ queryKey: ['lookups', 'islands', atollId], queryFn: () => lookupApi.getIslands(atollId || undefined) })
  const update = useMutation({
    mutationFn: async (values: FormValues) => {
      const user = await meApi.updateMe({
        name: values.name,
        id_card_number: values.id_card_number,
        email: values.email,
        atoll_id: values.atoll_id,
        island_id: values.island_id,
        house_name: values.house_name,
        floor: values.floor,
      })
      await meApi.replaceContactNumbers(
        [values.contact_number_1, values.contact_number_2, values.contact_number_3].filter((value): value is string => Boolean(value)),
      )
      return user
    },
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: ['me'] })
    },
  })

  useEffect(() => {
    if (me.data) {
      form.reset({
        name: me.data.name,
        id_card_number: me.data.id_card_number ?? '',
        email: me.data.email,
        atoll_id: me.data.atoll?.id ?? me.data.atoll_id ?? 0,
        island_id: me.data.island?.id ?? me.data.island_id ?? 0,
        house_name: me.data.house_name ?? '',
        floor: me.data.floor ?? '',
        contact_number_1: me.data.contact_numbers[0]?.number ?? '',
        contact_number_2: me.data.contact_numbers[1]?.number ?? '',
        contact_number_3: me.data.contact_numbers[2]?.number ?? '',
      })
    }
  }, [me.data, form])

  if (me.isLoading) {
    return <LoadingState label="Loading profile..." />
  }

  return (
    <div>
      <PageHeader title="Profile" description="Keep your contact and address details up to date." />
      <Card>
        <form className="grid gap-4" onSubmit={form.handleSubmit((values) => update.mutate(values))} noValidate>
          <Field label="Full name" error={form.formState.errors.name?.message ?? getFieldError(update.error, 'name')}>
            <input className={inputClassName} {...form.register('name')} />
          </Field>
          <div className="grid gap-4 md:grid-cols-2">
            <Field label="ID card number" error={form.formState.errors.id_card_number?.message ?? getFieldError(update.error, 'id_card_number')}>
              <input className={inputClassName} {...form.register('id_card_number')} />
            </Field>
            <Field label="Email" error={form.formState.errors.email?.message ?? getFieldError(update.error, 'email')}>
              <input className={inputClassName} type="email" {...form.register('email')} />
            </Field>
          </div>
          <div className="grid gap-4 md:grid-cols-2">
            <Field label="Atoll" error={form.formState.errors.atoll_id?.message ?? getFieldError(update.error, 'atoll_id')}>
              <select className={inputClassName} {...form.register('atoll_id')}>
                <option value={0}>Choose atoll</option>
                {atolls.data?.map((atoll) => (
                  <option key={atoll.id} value={atoll.id}>{atoll.code} - {atoll.name}</option>
                ))}
              </select>
            </Field>
            <Field label="Island" error={form.formState.errors.island_id?.message ?? getFieldError(update.error, 'island_id')}>
              <select className={inputClassName} {...form.register('island_id')}>
                <option value={0}>Choose island</option>
                {islands.data?.map((island) => (
                  <option key={island.id} value={island.id}>{island.name}</option>
                ))}
              </select>
            </Field>
          </div>
          <div className="grid gap-4 md:grid-cols-2">
            <Field label="House name" error={form.formState.errors.house_name?.message ?? getFieldError(update.error, 'house_name')}>
              <input className={inputClassName} {...form.register('house_name')} />
            </Field>
            <Field label="Floor" error={form.formState.errors.floor?.message ?? getFieldError(update.error, 'floor')}>
              <input className={inputClassName} {...form.register('floor')} />
            </Field>
          </div>
          <div className="grid gap-4 md:grid-cols-3">
            <Field label="Primary contact" error={form.formState.errors.contact_number_1?.message}>
              <input className={inputClassName} {...form.register('contact_number_1')} />
            </Field>
            <Field label="Second contact">
              <input className={inputClassName} {...form.register('contact_number_2')} />
            </Field>
            <Field label="Third contact">
              <input className={inputClassName} {...form.register('contact_number_3')} />
            </Field>
          </div>
          {update.isSuccess ? <p className="text-sm text-emerald-700">Profile saved.</p> : null}
          {update.isError ? <p className="text-sm text-rose-700">{getErrorMessage(update.error)}</p> : null}
          <Button type="submit" loading={update.isPending}>Save changes</Button>
        </form>
      </Card>
    </div>
  )
}

import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useForm } from 'react-hook-form'
import { z } from 'zod'
import { addressApi } from '../../api/addressApi'
import { Button } from '../../components/Button'
import { Card } from '../../components/Card'
import { EmptyState } from '../../components/EmptyState'
import { Field, inputClassName } from '../../components/Field'
import { LoadingState } from '../../components/LoadingState'
import { PageHeader } from '../../components/PageHeader'
import { getErrorMessage, getFieldError } from '../../lib/errors'

const schema = z.object({
  label: z.string().min(1, 'Label is required.'),
  purpose: z.enum(['pickup', 'drop_off']),
  line1: z.string().min(1, 'Address line is required.'),
  contact_name: z.string().min(1, 'Contact name is required.'),
  contact_phone: z.string().min(1, 'Contact phone is required.'),
  is_default: z.boolean(),
})

type FormValues = z.infer<typeof schema>

export default function AddressesPage() {
  const queryClient = useQueryClient()
  const addresses = useQuery({ queryKey: ['addresses'], queryFn: addressApi.listAddresses })
  const form = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: {
      label: '',
      purpose: 'pickup',
      line1: '',
      contact_name: '',
      contact_phone: '',
      is_default: false,
    },
  })
  const create = useMutation({
    mutationFn: (values: FormValues) =>
      addressApi.createAddress({
        label: values.label,
        purpose: values.purpose,
        address: { line1: values.line1 },
        contact_name: values.contact_name,
        contact_phone: values.contact_phone,
        is_default: values.is_default,
      }),
    onSuccess: async () => {
      form.reset()
      await queryClient.invalidateQueries({ queryKey: ['addresses'] })
    },
  })
  const remove = useMutation({
    mutationFn: addressApi.deleteAddress,
    onSuccess: async () => queryClient.invalidateQueries({ queryKey: ['addresses'] }),
  })

  return (
    <div>
      <PageHeader title="Saved addresses" description="Manage pickup and drop-off address shortcuts." />
      <div className="grid gap-4 lg:grid-cols-[1fr_360px]">
        <div className="grid gap-3 self-start">
          {addresses.isLoading ? <LoadingState label="Loading addresses..." /> : null}
          {!addresses.isLoading && addresses.data?.length === 0 ? <EmptyState title="No saved addresses" message="Add one to speed up future requests." /> : null}
          {addresses.data?.map((address) => (
            <Card key={address.id}>
              <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                  <h2 className="font-semibold text-slate-950">{address.label}</h2>
                  <p className="mt-1 text-sm capitalize text-slate-600">{address.purpose.replace('_', ' ')}</p>
                  <p className="mt-2 text-sm text-slate-700">{String(address.address.line1 ?? '')}</p>
                  <p className="text-sm text-slate-600">{address.contact_name} · {address.contact_phone}</p>
                </div>
                <Button type="button" variant="danger" loading={remove.isPending} onClick={() => remove.mutate(address.id)}>
                  Delete
                </Button>
              </div>
            </Card>
          ))}
        </div>
        <Card className="self-start">
          <h2 className="font-semibold text-slate-950">Add address</h2>
          <form className="mt-4 grid gap-4" onSubmit={form.handleSubmit((values) => create.mutate(values))} noValidate>
            <Field label="Label" error={form.formState.errors.label?.message ?? getFieldError(create.error, 'label')}>
              <input className={inputClassName} {...form.register('label')} />
            </Field>
            <Field label="Purpose" error={form.formState.errors.purpose?.message ?? getFieldError(create.error, 'purpose')}>
              <select className={inputClassName} {...form.register('purpose')}>
                <option value="pickup">Pickup</option>
                <option value="drop_off">Drop-off</option>
              </select>
            </Field>
            <Field label="Address line" error={form.formState.errors.line1?.message ?? getFieldError(create.error, 'address')}>
              <input className={inputClassName} {...form.register('line1')} />
            </Field>
            <Field label="Contact name" error={form.formState.errors.contact_name?.message ?? getFieldError(create.error, 'contact_name')}>
              <input className={inputClassName} {...form.register('contact_name')} />
            </Field>
            <Field label="Contact phone" error={form.formState.errors.contact_phone?.message ?? getFieldError(create.error, 'contact_phone')}>
              <input className={inputClassName} {...form.register('contact_phone')} />
            </Field>
            <label className="flex min-h-11 items-center gap-2 text-sm font-medium text-slate-800">
              <input type="checkbox" {...form.register('is_default')} />
              Default for this purpose
            </label>
            {create.isError ? <p className="text-sm text-rose-700">{getErrorMessage(create.error)}</p> : null}
            <Button type="submit" loading={create.isPending}>Save address</Button>
          </form>
        </Card>
      </div>
    </div>
  )
}

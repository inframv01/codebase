import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQuery } from '@tanstack/react-query'
import { useForm, useWatch } from 'react-hook-form'
import { Link, useNavigate } from 'react-router-dom'
import { z } from 'zod'
import { authApi } from '../../api/authApi'
import { lookupApi } from '../../api/lookupApi'
import { Button } from '../../components/Button'
import { Card } from '../../components/Card'
import { Field, inputClassName } from '../../components/Field'
import { getErrorMessage, getFieldError } from '../../lib/errors'

const schema = z.object({
  name: z.string().min(1, 'Name is required.'),
  id_card_number: z.string().min(1, 'ID card number is required.'),
  atoll_id: z.coerce.number().min(1, 'Choose an atoll.'),
  island_id: z.coerce.number().min(1, 'Choose an island.'),
  house_name: z.string().min(1, 'House name is required.'),
  floor: z.string().min(1, 'Floor is required.'),
  contact_number_1: z.string().min(1, 'Primary contact number is required.'),
  contact_number_2: z.string().optional(),
  contact_number_3: z.string().optional(),
  email: z.string().email('Enter a valid email address.'),
  password: z.string().min(8, 'Use at least 8 characters.'),
})

type FormInput = z.input<typeof schema>
type FormValues = z.output<typeof schema>

export default function RegisterPage() {
  const navigate = useNavigate()
  const form = useForm<FormInput, unknown, FormValues>({
    resolver: zodResolver(schema),
    defaultValues: {
      name: '',
      id_card_number: '',
      atoll_id: 0,
      island_id: 0,
      house_name: '',
      floor: '',
      contact_number_1: '',
      contact_number_2: '',
      contact_number_3: '',
      email: '',
      password: '',
    },
  })
  const atollId = Number(useWatch({ control: form.control, name: 'atoll_id' }) || 0)
  const atolls = useQuery({ queryKey: ['lookups', 'atolls'], queryFn: lookupApi.getAtolls })
  const islands = useQuery({ queryKey: ['lookups', 'islands', atollId], queryFn: () => lookupApi.getIslands(atollId || undefined) })
  const register = useMutation({
    mutationFn: authApi.register,
    onSuccess: (_, values) => navigate(`/otp?email=${encodeURIComponent(values.email)}`),
  })

  function submit(values: FormValues) {
    register.mutate({
      name: values.name,
      id_card_number: values.id_card_number,
      atoll_id: values.atoll_id,
      island_id: values.island_id,
      house_name: values.house_name,
      floor: values.floor,
      email: values.email,
      password: values.password,
      contact_numbers: [values.contact_number_1, values.contact_number_2, values.contact_number_3].filter(
        (value): value is string => Boolean(value),
      ),
    })
  }

  return (
    <Card>
      <h1 className="text-2xl font-bold text-slate-950">Create account</h1>
      <form className="mt-6 grid gap-4" onSubmit={form.handleSubmit(submit)} noValidate>
        <Field label="Full name" error={form.formState.errors.name?.message ?? getFieldError(register.error, 'name')}>
          <input className={inputClassName} {...form.register('name')} />
        </Field>
        <Field label="ID card number" error={form.formState.errors.id_card_number?.message ?? getFieldError(register.error, 'id_card_number')}>
          <input className={inputClassName} {...form.register('id_card_number')} />
        </Field>
        <Field label="Atoll" error={form.formState.errors.atoll_id?.message ?? getFieldError(register.error, 'atoll_id')}>
          <select className={inputClassName} {...form.register('atoll_id')}>
            <option value={0}>Choose atoll</option>
            {atolls.data?.map((atoll) => (
              <option key={atoll.id} value={atoll.id}>
                {atoll.code} - {atoll.name}
              </option>
            ))}
          </select>
        </Field>
        <Field label="Island" error={form.formState.errors.island_id?.message ?? getFieldError(register.error, 'island_id')}>
          <select className={inputClassName} {...form.register('island_id')}>
            <option value={0}>Choose island</option>
            {islands.data?.map((island) => (
              <option key={island.id} value={island.id}>
                {island.name}
              </option>
            ))}
          </select>
        </Field>
        <div className="grid gap-4 md:grid-cols-2">
          <Field label="House name" error={form.formState.errors.house_name?.message ?? getFieldError(register.error, 'house_name')}>
            <input className={inputClassName} {...form.register('house_name')} />
          </Field>
          <Field label="Floor" error={form.formState.errors.floor?.message ?? getFieldError(register.error, 'floor')}>
            <input className={inputClassName} {...form.register('floor')} />
          </Field>
        </div>
        <Field label="Primary contact" error={form.formState.errors.contact_number_1?.message ?? getFieldError(register.error, 'contact_numbers.0')}>
          <input className={inputClassName} {...form.register('contact_number_1')} />
        </Field>
        <div className="grid gap-4 md:grid-cols-2">
          <Field label="Second contact" error={getFieldError(register.error, 'contact_numbers.1')}>
            <input className={inputClassName} {...form.register('contact_number_2')} />
          </Field>
          <Field label="Third contact" error={getFieldError(register.error, 'contact_numbers.2')}>
            <input className={inputClassName} {...form.register('contact_number_3')} />
          </Field>
        </div>
        <Field label="Email" error={form.formState.errors.email?.message ?? getFieldError(register.error, 'email')}>
          <input className={inputClassName} type="email" {...form.register('email')} />
        </Field>
        <Field label="Password" error={form.formState.errors.password?.message ?? getFieldError(register.error, 'password')}>
          <input className={inputClassName} type="password" {...form.register('password')} />
        </Field>
        {register.isError ? <p className="text-sm text-rose-700">{getErrorMessage(register.error)}</p> : null}
        <Button type="submit" loading={register.isPending}>
          Register
        </Button>
        <Link className="text-sm font-semibold text-teal-700" to="/login">
          Already have an account?
        </Link>
      </form>
    </Card>
  )
}

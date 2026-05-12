import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation } from '@tanstack/react-query'
import { useForm } from 'react-hook-form'
import { Link, useSearchParams } from 'react-router-dom'
import { z } from 'zod'
import { authApi } from '../../api/authApi'
import { Button } from '../../components/Button'
import { Card } from '../../components/Card'
import { Field, inputClassName } from '../../components/Field'
import { getErrorMessage, getFieldError } from '../../lib/errors'

const forgotSchema = z.object({ email: z.string().email('Enter a valid email address.') })
const resetSchema = z.object({
  token: z.string().min(1, 'Reset token is required.'),
  email: z.string().email('Enter a valid email address.'),
  password: z.string().min(8, 'Use at least 8 characters.'),
  password_confirmation: z.string().min(8, 'Confirm your password.'),
})

type ForgotValues = z.infer<typeof forgotSchema>
type ResetValues = z.infer<typeof resetSchema>

export function ForgotPasswordPage() {
  const form = useForm<ForgotValues>({ resolver: zodResolver(forgotSchema), defaultValues: { email: '' } })
  const forgot = useMutation({ mutationFn: (values: ForgotValues) => authApi.forgotPassword(values.email) })

  return (
    <Card>
      <h1 className="text-2xl font-bold text-slate-950">Reset password</h1>
      <form className="mt-6 grid gap-4" onSubmit={form.handleSubmit((values) => forgot.mutate(values))} noValidate>
        <Field label="Email" error={form.formState.errors.email?.message ?? getFieldError(forgot.error, 'email')}>
          <input className={inputClassName} type="email" {...form.register('email')} />
        </Field>
        {forgot.isSuccess ? <p className="text-sm text-emerald-700">Check your email for the reset link.</p> : null}
        {forgot.isError ? <p className="text-sm text-rose-700">{getErrorMessage(forgot.error)}</p> : null}
        <Button type="submit" loading={forgot.isPending}>
          Email reset link
        </Button>
        <Link className="text-sm font-semibold text-teal-700" to="/login">
          Back to sign in
        </Link>
      </form>
    </Card>
  )
}

export function ResetPasswordPage() {
  const [params] = useSearchParams()
  const form = useForm<ResetValues>({
    resolver: zodResolver(resetSchema),
    defaultValues: {
      token: params.get('token') ?? '',
      email: params.get('email') ?? '',
      password: '',
      password_confirmation: '',
    },
  })
  const reset = useMutation({ mutationFn: authApi.resetPassword })

  return (
    <Card>
      <h1 className="text-2xl font-bold text-slate-950">Choose new password</h1>
      <form className="mt-6 grid gap-4" onSubmit={form.handleSubmit((values) => reset.mutate(values))} noValidate>
        <Field label="Token" error={form.formState.errors.token?.message ?? getFieldError(reset.error, 'token')}>
          <input className={inputClassName} {...form.register('token')} />
        </Field>
        <Field label="Email" error={form.formState.errors.email?.message ?? getFieldError(reset.error, 'email')}>
          <input className={inputClassName} type="email" {...form.register('email')} />
        </Field>
        <Field label="Password" error={form.formState.errors.password?.message ?? getFieldError(reset.error, 'password')}>
          <input className={inputClassName} type="password" {...form.register('password')} />
        </Field>
        <Field label="Confirm password" error={form.formState.errors.password_confirmation?.message}>
          <input className={inputClassName} type="password" {...form.register('password_confirmation')} />
        </Field>
        {reset.isSuccess ? <p className="text-sm text-emerald-700">Password reset. You can sign in now.</p> : null}
        {reset.isError ? <p className="text-sm text-rose-700">{getErrorMessage(reset.error)}</p> : null}
        <Button type="submit" loading={reset.isPending}>
          Save password
        </Button>
      </form>
    </Card>
  )
}

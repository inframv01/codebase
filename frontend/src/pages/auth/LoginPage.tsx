import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation } from '@tanstack/react-query'
import { useForm } from 'react-hook-form'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { z } from 'zod'
import { authApi } from '../../api/authApi'
import { useAuth } from '../../auth/useAuth'
import { Button } from '../../components/Button'
import { Card } from '../../components/Card'
import { Field, inputClassName } from '../../components/Field'
import { getErrorMessage, getFieldError } from '../../lib/errors'

const schema = z.object({
  email: z.string().email('Enter a valid email address.'),
  password: z.string().min(1, 'Password is required.'),
})

type FormValues = z.infer<typeof schema>

interface LocationState {
  from?: { pathname?: string }
}

export default function LoginPage() {
  const auth = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const from = (location.state as LocationState | null)?.from?.pathname ?? '/'
  const form = useForm<FormValues>({ resolver: zodResolver(schema), defaultValues: { email: '', password: '' } })
  const login = useMutation({
    mutationFn: auth.signIn,
    onSuccess: (response) => navigate(response.user.role === 'operator' || response.user.role === 'admin' ? '/operator' : from),
  })
  const google = useMutation({
    mutationFn: authApi.googleRedirect,
    onSuccess: ({ url }) => {
      window.location.href = url
    },
  })

  return (
    <Card>
      <h1 className="text-2xl font-bold text-slate-950">Sign in</h1>
      <p className="mt-2 text-sm text-slate-600">Access your deliveries, payments, and operator tools.</p>
      <form className="mt-6 grid gap-4" onSubmit={form.handleSubmit((values) => login.mutate(values))} noValidate>
        <Field label="Email" error={form.formState.errors.email?.message ?? getFieldError(login.error, 'email')}>
          <input className={inputClassName} type="email" autoComplete="email" {...form.register('email')} />
        </Field>
        <Field label="Password" error={form.formState.errors.password?.message ?? getFieldError(login.error, 'password')}>
          <input className={inputClassName} type="password" autoComplete="current-password" {...form.register('password')} />
        </Field>
        {login.isError ? <p className="text-sm text-rose-700">{getErrorMessage(login.error)}</p> : null}
        <Button type="submit" loading={login.isPending}>
          Sign in
        </Button>
      </form>
      <div className="mt-4 grid gap-3">
        <Button type="button" variant="secondary" loading={google.isPending} onClick={() => google.mutate()}>
          Continue with Google
        </Button>
        <div className="flex flex-wrap gap-3 text-sm">
          <Link className="font-semibold text-teal-700" to="/register">
            Create account
          </Link>
          <Link className="font-semibold text-teal-700" to="/forgot-password">
            Forgot password
          </Link>
        </div>
      </div>
    </Card>
  )
}

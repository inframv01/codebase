import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation } from '@tanstack/react-query'
import { useForm } from 'react-hook-form'
import { useNavigate, useSearchParams } from 'react-router-dom'
import { z } from 'zod'
import { authApi } from '../../api/authApi'
import { useAuth } from '../../auth/useAuth'
import { Button } from '../../components/Button'
import { Card } from '../../components/Card'
import { Field, inputClassName } from '../../components/Field'
import { getErrorMessage, getFieldError } from '../../lib/errors'

const schema = z.object({
  email: z.string().email('Enter a valid email address.'),
  code: z.string().regex(/^\d{6}$/, 'Enter the 6 digit code.'),
})

type FormValues = z.infer<typeof schema>

export default function OtpPage() {
  const [params] = useSearchParams()
  const auth = useAuth()
  const navigate = useNavigate()
  const form = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: { email: params.get('email') ?? '', code: '' },
  })
  const verify = useMutation({
    mutationFn: (values: FormValues) => authApi.verifyOtp(values.email, values.code),
    onSuccess: (response) => {
      auth.applyAuth(response)
      navigate('/')
    },
  })
  const resend = useMutation({ mutationFn: (email: string) => authApi.resendOtp(email) })

  return (
    <Card>
      <h1 className="text-2xl font-bold text-slate-950">Verify email</h1>
      <p className="mt-2 text-sm text-slate-600">Enter the code sent to your email address.</p>
      <form className="mt-6 grid gap-4" onSubmit={form.handleSubmit((values) => verify.mutate(values))} noValidate>
        <Field label="Email" error={form.formState.errors.email?.message ?? getFieldError(verify.error, 'email')}>
          <input className={inputClassName} type="email" {...form.register('email')} />
        </Field>
        <Field label="OTP code" error={form.formState.errors.code?.message ?? getFieldError(verify.error, 'code')}>
          <input className={inputClassName} inputMode="numeric" {...form.register('code')} />
        </Field>
        {verify.isError ? <p className="text-sm text-rose-700">{getErrorMessage(verify.error)}</p> : null}
        {resend.isSuccess ? <p className="text-sm text-emerald-700">A new code has been sent.</p> : null}
        <Button type="submit" loading={verify.isPending}>
          Verify email
        </Button>
        <Button type="button" variant="secondary" loading={resend.isPending} onClick={() => resend.mutate(form.getValues('email'))}>
          Resend code
        </Button>
      </form>
    </Card>
  )
}

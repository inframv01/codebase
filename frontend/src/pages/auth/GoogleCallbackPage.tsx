import { useMutation } from '@tanstack/react-query'
import { useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { authApi } from '../../api/authApi'
import { useAuth } from '../../auth/useAuth'
import { Card } from '../../components/Card'
import { getErrorMessage } from '../../lib/errors'

export default function GoogleCallbackPage() {
  const auth = useAuth()
  const navigate = useNavigate()
  const callback = useMutation({
    mutationFn: () => authApi.googleCallback(window.location.search),
    onSuccess: (response) => {
      auth.applyAuth(response)
      navigate(response.user.role === 'operator' || response.user.role === 'admin' ? '/operator' : '/')
    },
  })
  const { mutate } = callback

  useEffect(() => {
    mutate()
  }, [mutate])

  return (
    <Card>
      <h1 className="text-2xl font-bold text-slate-950">Signing you in</h1>
      <p className="mt-2 text-sm text-slate-600">
        {callback.isError ? getErrorMessage(callback.error) : 'Completing Google authentication...'}
      </p>
    </Card>
  )
}

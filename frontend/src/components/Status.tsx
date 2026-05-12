import { Badge } from './Badge'

interface StatusProps {
  value?: string | null
}

export function Status({ value }: StatusProps) {
  const normalized = value ?? 'unknown'
  const tone =
    normalized.includes('delivered') || normalized.includes('verified')
      ? 'success'
      : normalized.includes('cancel') || normalized.includes('reject')
        ? 'danger'
        : normalized.includes('awaiting') || normalized.includes('pending')
          ? 'warning'
          : 'info'

  return <Badge tone={tone}>{normalized.replaceAll('_', ' ')}</Badge>
}

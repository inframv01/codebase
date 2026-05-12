export function formatMoney(cents?: number | null): string {
  if (cents === null || cents === undefined) {
    return 'Inspection required'
  }

  return new Intl.NumberFormat('en-MV', {
    style: 'currency',
    currency: 'MVR',
  }).format(cents / 100)
}

export function formatDateTime(value?: string | null): string {
  if (!value) {
    return 'Not set'
  }

  return new Intl.DateTimeFormat('en-MV', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value))
}

export function humanize(value?: string | null): string {
  if (!value) {
    return 'Not set'
  }

  return value.replaceAll('_', ' ')
}

import type { User } from '../types'

const STORAGE_KEY = 'maldiv_delivery_auth_v1'

export interface StoredAuth {
  token: string
  user: User
}

export function loadStoredAuth(): StoredAuth | null {
  const raw = window.localStorage.getItem(STORAGE_KEY)

  if (!raw) {
    return null
  }

  try {
    const parsed: unknown = JSON.parse(raw)

    if (
      typeof parsed === 'object' &&
      parsed !== null &&
      'token' in parsed &&
      'user' in parsed &&
      typeof parsed.token === 'string'
    ) {
      return parsed as StoredAuth
    }
  } catch {
    window.localStorage.removeItem(STORAGE_KEY)
  }

  return null
}

export function saveStoredAuth(auth: StoredAuth): void {
  window.localStorage.setItem(STORAGE_KEY, JSON.stringify(auth))
}

export function clearStoredAuth(): void {
  window.localStorage.removeItem(STORAGE_KEY)
}

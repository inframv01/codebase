import axios from 'axios'
import { clearStoredAuth, loadStoredAuth } from './authStorage'

const rawApiBaseUrl = import.meta.env.VITE_API_BASE_URL?.trim()
const normalizedApiBaseUrl = rawApiBaseUrl
  ? rawApiBaseUrl.replace(/\/+$/, '').replace(/\/api(\/v\d+)?$/, '')
  : undefined
const legacyApiUrl = import.meta.env.VITE_API_URL?.trim()
const normalizedLegacyApiUrl = legacyApiUrl
  ? legacyApiUrl.replace(/\/+$/, '').replace(/\/api(\/v\d+)?$/, '')
  : undefined
const baseURL = normalizedApiBaseUrl
  ? `${normalizedApiBaseUrl}/api`
  : normalizedLegacyApiUrl
    ? `${normalizedLegacyApiUrl}/api`
    : '/api'

export const api = axios.create({
  baseURL,
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
})

api.interceptors.request.use((config) => {
  const auth = loadStoredAuth()

  if (auth?.token) {
    config.headers.Authorization = `Bearer ${auth.token}`
  }

  return config
})

api.interceptors.response.use(
  (response) => response,
  (error: unknown) => {
    if (axios.isAxiosError(error) && error.response?.status === 401) {
      clearStoredAuth()
      window.dispatchEvent(new Event('auth:expired'))
    }

    return Promise.reject(error)
  },
)

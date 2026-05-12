import { isAxiosError } from 'axios'
import type { ApiValidationError } from '../types'

interface MessageError {
  message: string
}

export function getErrorMessage(error: unknown, fallback = 'Something went wrong. Please try again.'): string {
  if (isAxiosError<MessageError>(error) && error.response?.data?.message) {
    return error.response.data.message
  }

  if (error instanceof Error) {
    return error.message
  }

  return fallback
}

export function getFieldError(error: unknown, field: string): string | undefined {
  if (isAxiosError<ApiValidationError>(error) && error.response?.status === 422) {
    return error.response.data.errors[field]?.[0]
  }

  return undefined
}

export function getValidationErrors(error: unknown): Record<string, string[]> {
  if (isAxiosError<ApiValidationError>(error) && error.response?.status === 422) {
    return error.response.data.errors
  }

  return {}
}

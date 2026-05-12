import type { ApiMessageResponse, PaginatedResponse } from '../types'

interface UnknownRecord {
  [key: string]: unknown
}

function isRecord(value: unknown): value is UnknownRecord {
  return typeof value === 'object' && value !== null
}

function hasDataWrapper(value: unknown): value is { data: unknown } {
  return isRecord(value) && 'data' in value
}

export function unwrapData(payload: unknown): unknown {
  return hasDataWrapper(payload) ? payload.data : payload
}

export function asArray<TItem>(payload: unknown): TItem[] {
  const unwrapped = unwrapData(payload)
  return Array.isArray(unwrapped) ? (unwrapped as TItem[]) : []
}

function toNumber(value: unknown, fallback: number): number {
  return typeof value === 'number' && Number.isFinite(value) ? value : fallback
}

function toNullableString(value: unknown): string | null {
  return typeof value === 'string' ? value : null
}

export function emptyPaginatedResponse<TItem>(): PaginatedResponse<TItem> {
  return {
    data: [],
    links: {
      first: null,
      last: null,
      prev: null,
      next: null,
    },
    meta: {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
      from: null,
      to: null,
      path: '',
    },
  }
}

export function asPaginated<TItem>(payload: unknown): PaginatedResponse<TItem> {
  const unwrapped = unwrapData(payload)

  if (Array.isArray(unwrapped)) {
    return {
      ...emptyPaginatedResponse<TItem>(),
      data: unwrapped as TItem[],
      meta: {
        ...emptyPaginatedResponse<TItem>().meta,
        total: unwrapped.length,
        to: unwrapped.length,
      },
    }
  }

  if (!isRecord(unwrapped)) {
    return emptyPaginatedResponse<TItem>()
  }

  const data = Array.isArray(unwrapped.data) ? (unwrapped.data as TItem[]) : []
  const links = isRecord(unwrapped.links) ? unwrapped.links : {}
  const meta = isRecord(unwrapped.meta) ? unwrapped.meta : {}

  return {
    data,
    links: {
      first: toNullableString(links.first),
      last: toNullableString(links.last),
      prev: toNullableString(links.prev),
      next: toNullableString(links.next),
    },
    meta: {
      current_page: toNumber(meta.current_page, 1),
      last_page: toNumber(meta.last_page, 1),
      per_page: toNumber(meta.per_page, data.length || 15),
      total: toNumber(meta.total, data.length),
      from: typeof meta.from === 'number' ? meta.from : null,
      to: typeof meta.to === 'number' ? meta.to : null,
      path: typeof meta.path === 'string' ? meta.path : '',
    },
  }
}

export function asObject<TObject extends object>(payload: unknown): TObject {
  const unwrapped = unwrapData(payload)

  if (isRecord(unwrapped)) {
    return unwrapped as TObject
  }

  throw new Error('Unexpected API response shape.')
}

export function asMessage(payload: unknown, fallback = 'Request completed.'): ApiMessageResponse {
  const object = asObject<{ message?: unknown }>(payload)
  return {
    message: typeof object.message === 'string' ? object.message : fallback,
  }
}
import { Button } from './Button'
import type { PaginatedResponse } from '../types'

interface PaginationProps<T> {
  page: PaginatedResponse<T>['meta']
  onPageChange: (page: number) => void
}

export function Pagination<T>({ page, onPageChange }: PaginationProps<T>) {
  return (
    <div className="flex items-center justify-between gap-3 border-t border-slate-200 pt-4">
      <Button
        type="button"
        variant="secondary"
        disabled={page.current_page <= 1}
        onClick={() => onPageChange(page.current_page - 1)}
      >
        Previous
      </Button>
      <span className="text-sm text-slate-600">
        Page {page.current_page} of {page.last_page}
      </span>
      <Button
        type="button"
        variant="secondary"
        disabled={page.current_page >= page.last_page}
        onClick={() => onPageChange(page.current_page + 1)}
      >
        Next
      </Button>
    </div>
  )
}

import { Package } from 'lucide-react'
import { Link, Outlet } from 'react-router-dom'

export function PublicLayout() {
  return (
    <div className="min-h-dvh bg-slate-50 px-4 py-6 text-slate-950">
      <div className="mx-auto flex w-full max-w-md flex-col gap-6">
        <Link to="/login" className="flex min-h-11 items-center gap-2 font-bold">
          <Package className="h-6 w-6 text-teal-700" aria-hidden="true" />
          Maldiv Delivery
        </Link>
        <Outlet />
      </div>
    </div>
  )
}

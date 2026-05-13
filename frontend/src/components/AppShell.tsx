import { Bell, LogOut, Menu, Package, UserRound, X } from 'lucide-react'
import { useState } from 'react'
import { Link, NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../auth/useAuth'
import { Button } from './Button'

const customerLinks = [
  { to: '/', label: 'Dashboard' },
  { to: '/deliveries', label: 'Deliveries' },
  { to: '/addresses', label: 'Addresses' },
  { to: '/profile', label: 'Profile' },
]

const operatorLinks = [
  { to: '/operator', label: 'Operator' },
  { to: '/operator/deliveries', label: 'Queue' },
  { to: '/operator/resources/atolls', label: 'Atolls' },
  { to: '/operator/resources/islands', label: 'Islands' },
  { to: '/operator/resources/transport-providers', label: 'Providers' },
  { to: '/operator/resources/boats', label: 'Boats' },
  { to: '/operator/resources/pricing-rules', label: 'Pricing' },
]

function linkClassName({ isActive }: { isActive: boolean }) {
  return `rounded-lg px-3 py-2 text-sm font-semibold transition ${
    isActive ? 'bg-teal-700 text-white' : 'text-slate-700 hover:bg-slate-100'
  }`
}

export function AppShell() {
  const auth = useAuth()
  const navigate = useNavigate()
  const [menuOpen, setMenuOpen] = useState(false)
  const links = auth.isOperator ? [...customerLinks, ...operatorLinks] : customerLinks

  function handleSignOut() {
    auth.signOut()
    navigate('/login')
  }

  return (
    <div className="min-h-dvh bg-slate-50 text-slate-950">
      <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div className="mx-auto flex h-14 max-w-screen-lg items-center justify-between gap-3 px-4 md:px-6 lg:px-8">
          <Link to="/" className="flex min-h-11 items-center gap-2 font-bold text-slate-950">
            <Package className="h-5 w-5 text-teal-700" aria-hidden="true" />
            Fast-delivery
          </Link>
          <nav className="hidden items-center gap-1 md:flex" aria-label="Primary">
            {links.map((link) => (
              <NavLink key={link.to} to={link.to} className={linkClassName} end={link.to === '/' || link.to === '/operator'}>
                {link.label}
              </NavLink>
            ))}
          </nav>
          <div className="flex items-center gap-1">
            <Link
              to="/notifications"
              className="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg text-slate-700 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600"
              aria-label="Notifications"
            >
              <Bell className="h-5 w-5" aria-hidden="true" />
            </Link>
            <Link
              to="/profile"
              className="hidden min-h-11 items-center gap-2 rounded-lg px-3 text-sm font-semibold text-slate-700 hover:bg-slate-100 md:inline-flex"
            >
              <UserRound className="h-4 w-4" aria-hidden="true" />
              {auth.user?.name ?? 'Profile'}
            </Link>
            <button
              type="button"
              className="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg text-slate-700 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 md:hidden"
              onClick={() => setMenuOpen((open) => !open)}
              aria-label={menuOpen ? 'Close menu' : 'Open menu'}
              aria-expanded={menuOpen}
            >
              {menuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
            </button>
            <Button type="button" variant="ghost" className="hidden md:inline-flex" onClick={handleSignOut}>
              <LogOut className="h-4 w-4" aria-hidden="true" />
              Sign out
            </Button>
          </div>
        </div>
        {menuOpen ? (
          <nav className="border-t border-slate-200 bg-white px-4 py-3 md:hidden" aria-label="Mobile primary">
            <div className="mx-auto grid max-w-screen-lg gap-1">
              {links.map((link) => (
                <NavLink
                  key={link.to}
                  to={link.to}
                  className={linkClassName}
                  end={link.to === '/' || link.to === '/operator'}
                  onClick={() => setMenuOpen(false)}
                >
                  {link.label}
                </NavLink>
              ))}
              <button
                type="button"
                className="min-h-11 rounded-lg px-3 py-2 text-left text-sm font-semibold text-slate-700 hover:bg-slate-100"
                onClick={handleSignOut}
              >
                Sign out
              </button>
            </div>
          </nav>
        ) : null}
      </header>
      <main className="mx-auto w-full max-w-screen-lg px-4 py-5 md:px-6 md:py-6 lg:px-8">
        <Outlet />
      </main>
    </div>
  )
}

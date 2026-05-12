import { Loader2 } from 'lucide-react'

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost'
  loading?: boolean
}

const variants = {
  primary: 'border-teal-700 bg-teal-700 text-white hover:bg-teal-800',
  secondary: 'border-slate-300 bg-white text-slate-950 hover:bg-slate-50',
  danger: 'border-rose-600 bg-rose-600 text-white hover:bg-rose-700',
  ghost: 'border-transparent bg-transparent text-slate-700 hover:bg-slate-100',
}

export function Button({ variant = 'primary', loading = false, className = '', children, disabled, ...props }: ButtonProps) {
  return (
    <button
      className={`inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border px-4 py-2 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 ${variants[variant]} ${className}`}
      disabled={disabled || loading}
      {...props}
    >
      {loading ? <Loader2 className="h-4 w-4 animate-spin" aria-hidden="true" /> : null}
      {children}
    </button>
  )
}

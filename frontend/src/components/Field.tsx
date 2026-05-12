interface FieldProps {
  label: string
  error?: string
  children: React.ReactNode
}

export function Field({ label, error, children }: FieldProps) {
  return (
    <label className="grid gap-2 text-sm font-medium text-slate-800">
      <span>{label}</span>
      {children}
      {error ? (
        <span className="text-sm font-normal text-rose-700" role="alert">
          {error}
        </span>
      ) : null}
    </label>
  )
}

export const inputClassName =
  'min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-base text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 disabled:bg-slate-100'

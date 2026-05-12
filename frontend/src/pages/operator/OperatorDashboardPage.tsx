import { Link } from 'react-router-dom'
import { Card } from '../../components/Card'
import { PageHeader } from '../../components/PageHeader'

const links = [
  { to: '/operator/deliveries', label: 'Delivery queue', description: 'Quote, accept, stage, and review payments.' },
  { to: '/operator/resources/atolls', label: 'Atolls', description: 'Manage atoll codes and names.' },
  { to: '/operator/resources/islands', label: 'Islands', description: 'Maintain island lookup records.' },
  { to: '/operator/resources/island-groups', label: 'Island groups', description: 'Group islands for transport coverage.' },
  { to: '/operator/resources/transport-providers', label: 'Transport providers', description: 'Manage active transport operators.' },
  { to: '/operator/resources/boats', label: 'Boats', description: 'Manage boats and schedule access.' },
  { to: '/operator/resources/pricing-rules', label: 'Pricing rules', description: 'Configure quote behavior and charges.' },
]

export default function OperatorDashboardPage() {
  return (
    <div>
      <PageHeader title="Operator dashboard" description="Manage delivery workflow and platform configuration." />
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {links.map((link) => (
          <Link key={link.to} to={link.to} className="block">
            <Card className="h-full">
              <h2 className="font-semibold text-slate-950">{link.label}</h2>
              <p className="mt-2 text-sm leading-6 text-slate-600">{link.description}</p>
            </Card>
          </Link>
        ))}
      </div>
    </div>
  )
}

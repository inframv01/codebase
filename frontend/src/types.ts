export interface ApiValidationError {
  message: string
  errors: Record<string, string[]>
}

export interface ApiMessageResponse {
  message: string
}

export interface PaginatedResponse<T> {
  data: T[]
  links: {
    first: string | null
    last: string | null
    prev: string | null
    next: string | null
  }
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from?: number | null
    to?: number | null
    path?: string
  }
}

export type UserRole = 'customer' | 'operator' | 'admin'
export type ServiceType = 'post_office' | 'male_address' | 'shop'
export type AddressPurpose = 'pickup' | 'drop_off'
export type DeliveryStatus =
  | 'pending_quote'
  | 'awaiting_payment'
  | 'payment_uploaded'
  | 'accepted'
  | 'in_transit'
  | 'delivered'
  | 'cancelled'
  | string
export type DeliveryStage =
  | 'accepted_by_operator'
  | 'picked_up'
  | 'in_transit'
  | 'arrived_at_island'
  | 'out_for_delivery'
  | 'delivered'
  | 'cancelled'
export type PaymentStatus = 'pending' | 'verified' | 'rejected' | string

export interface ContactNumber {
  id?: number
  user_id?: number
  number: string
  position: number
  created_at?: string
  updated_at?: string
}

export interface Atoll {
  id: number
  code: string
  name: string
}

export interface Island {
  id: number
  name: string
  atoll?: Atoll
}

export interface IslandGroup {
  id: number
  name: string
  islands?: Island[]
}

export interface TransportProvider {
  id: number
  name: string
  type: 'boat' | 'inter_island' | 'inter_atoll' | string
  contact_name?: string | null
  contact_phone?: string | null
  active: boolean
}

export interface Boat {
  id: number
  name: string
  capacity_kg?: string | null
  active: boolean
  transport_provider?: Pick<TransportProvider, 'id' | 'name'>
  island_groups?: IslandGroup[]
}

export interface BoatSchedule {
  id: number
  status: 'scheduled' | 'departed' | 'arrived' | 'cancelled' | string
  departs_at: string
  arrives_at?: string | null
  capacity_remaining_kg?: string | null
  boat?: Pick<Boat, 'id' | 'name'>
  origin_island?: Pick<Island, 'id' | 'name'>
  destination_island?: Pick<Island, 'id' | 'name'>
}

export interface PricingRule {
  id: number
  scope_type: 'island' | 'island_group'
  scope_id: number
  service_type: ServiceType
  fixed_cost_cents: number
  variable_rate_cents_per_kg: number
  min_charge_cents: number
  requires_inspection: boolean
  active: boolean
}

export interface User {
  id: number
  name: string
  id_card_number?: string
  atoll_id?: number
  island_id?: number
  house_name?: string
  floor?: string
  email: string
  google_id?: string | null
  role: UserRole
  email_verified_at?: string | null
  created_at?: string
  updated_at?: string
  atoll?: Atoll
  island?: Island
  contact_numbers: ContactNumber[]
}

export interface AuthResponse extends ApiMessageResponse {
  token: string
  user: User
}

export interface SavedAddress {
  id: number
  label: string
  purpose: AddressPurpose
  address: Record<string, unknown>
  contact_name: string
  contact_phone: string
  is_default: boolean
}

export interface QuotePreview {
  fixed_cost_cents: number
  variable_cost_cents: number | null
  min_charge_cents: number
  total_cost_cents: number | null
  requires_inspection: boolean
  pricing_rule_id: number
}

export interface Payment {
  uuid: string
  amount_cents: number
  slip_path?: string
  status: PaymentStatus
  verified_at?: string | null
  rejection_reason?: string | null
}

export interface DeliveryStageEvent {
  id?: number
  stage?: DeliveryStage | string
  notes?: string | null
  created_at?: string
}

export interface DeliveryRequest {
  uuid: string
  type: ServiceType
  status: DeliveryStatus
  current_stage?: DeliveryStage | string | null
  requires_inspection?: boolean
  fixed_cost_cents?: number | null
  variable_cost_cents?: number | null
  total_cost_cents?: number | null
  quote_confirmed_at?: string | null
  notes?: string | null
  destination_island?: Pick<Island, 'id' | 'name'>
  transport_provider?: Pick<TransportProvider, 'id' | 'name'> | null
  boat_schedule?: Pick<BoatSchedule, 'id' | 'status' | 'departs_at'> | null
  details?: Record<string, unknown>
  stage_events?: DeliveryStageEvent[]
  payments?: Payment[]
  accepted_by_operator_id?: number
}

export interface DatabaseNotification {
  id: string
  type: string
  data: Record<string, unknown>
  read_at: string | null
  created_at: string
}

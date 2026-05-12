import { Navigate, Route, Routes } from 'react-router-dom'
import { AppShell } from './components/AppShell'
import { PublicLayout } from './components/PublicLayout'
import { RequireAuth, RequireOperator } from './auth/RequireAuth'
import LoginPage from './pages/auth/LoginPage'
import RegisterPage from './pages/auth/RegisterPage'
import OtpPage from './pages/auth/OtpPage'
import GoogleCallbackPage from './pages/auth/GoogleCallbackPage'
import { ForgotPasswordPage, ResetPasswordPage } from './pages/auth/PasswordPage'
import DashboardPage from './pages/customer/DashboardPage'
import DeliveriesPage from './pages/customer/DeliveriesPage'
import DeliveryCreatePage from './pages/customer/DeliveryCreatePage'
import DeliveryDetailPage from './pages/customer/DeliveryDetailPage'
import AddressesPage from './pages/customer/AddressesPage'
import ProfilePage from './pages/customer/ProfilePage'
import NotificationsPage from './pages/customer/NotificationsPage'
import OperatorDashboardPage from './pages/operator/OperatorDashboardPage'
import OperatorDeliveriesPage from './pages/operator/OperatorDeliveriesPage'
import OperatorDeliveryDetailPage from './pages/operator/OperatorDeliveryDetailPage'
import OperatorResourcePage from './pages/operator/OperatorResourcePage'
import BoatSchedulesPage from './pages/operator/BoatSchedulesPage'

export default function App() {
  return (
    <Routes>
      <Route element={<PublicLayout />}>
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route path="/otp" element={<OtpPage />} />
        <Route path="/forgot-password" element={<ForgotPasswordPage />} />
        <Route path="/reset-password" element={<ResetPasswordPage />} />
        <Route path="/auth/google/callback" element={<GoogleCallbackPage />} />
      </Route>
      <Route element={<RequireAuth />}>
        <Route element={<AppShell />}>
          <Route index element={<DashboardPage />} />
          <Route path="/deliveries" element={<DeliveriesPage />} />
          <Route path="/deliveries/new" element={<DeliveryCreatePage />} />
          <Route path="/deliveries/:uuid" element={<DeliveryDetailPage />} />
          <Route path="/addresses" element={<AddressesPage />} />
          <Route path="/profile" element={<ProfilePage />} />
          <Route path="/notifications" element={<NotificationsPage />} />
        </Route>
      </Route>
      <Route element={<RequireOperator />}>
        <Route element={<AppShell />}>
          <Route path="/operator" element={<OperatorDashboardPage />} />
          <Route path="/operator/deliveries" element={<OperatorDeliveriesPage />} />
          <Route path="/operator/deliveries/:uuid" element={<OperatorDeliveryDetailPage />} />
          <Route path="/operator/resources/:resource" element={<OperatorResourcePage />} />
          <Route path="/operator/boats/:boatId/schedules" element={<BoatSchedulesPage />} />
        </Route>
      </Route>
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  )
}

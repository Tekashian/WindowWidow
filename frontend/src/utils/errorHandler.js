import { useToast } from '@/composables/useToast'

export function setupErrorHandler(app) {
  // Global Vue error handler
  app.config.errorHandler = (err, instance, info) => {
    console.error('Vue Error:', err)
    console.error('Component:', instance)
    console.error('Error Info:', info)
    
    const { error } = useToast()
    error(`Błąd aplikacji: ${err.message}`)
  }

  // Global unhandled rejection handler
  window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled Promise Rejection:', event.reason)
    
    const { error } = useToast()
    error(`Nieobsłużony błąd: ${event.reason?.message || 'Nieznany błąd'}`)
  })
}

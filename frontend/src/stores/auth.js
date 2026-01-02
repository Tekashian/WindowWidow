import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api, { authAPI } from '../services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))
  const tokenExpiry = ref(localStorage.getItem('tokenExpiry'))
  const isAuthenticated = computed(() => {
    // Sprawdź czy token istnieje i nie wygasł
    if (!token.value || !tokenExpiry.value) return false
    const now = Date.now()
    const expiry = parseInt(tokenExpiry.value)
    return now < expiry
  })
  const error = ref(null)

  async function login(credentials) {
    try {
      error.value = null
      const response = await authAPI.login(credentials)
      user.value = response.data.user
      token.value = response.data.token
      
      // Token wygasa po 30 minutach
      const expiry = Date.now() + (30 * 60 * 1000) // 30 minut w ms
      tokenExpiry.value = expiry.toString()
      
      localStorage.setItem('token', token.value)
      localStorage.setItem('tokenExpiry', tokenExpiry.value)
      localStorage.setItem('user', JSON.stringify(user.value))
      
      return { success: true }
    } catch (err) {
      console.error('Login failed:', err)
      error.value = err.response?.data?.message || 'Login failed'
      return { success: false, error: error.value }
    }
  }

  async function logout() {
    try {
      if (token.value) {
        await authAPI.logout()
      }
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      user.value = null
      token.value = null
      tokenExpiry.value = null
      localStorage.removeItem('token')
      localStorage.removeItem('tokenExpiry')
      localStorage.removeItem('user')
    }
  }

  async function fetchUser() {
    if (!token.value) {
      return
    }

    // Sprawdź czy token nie wygasł
    if (!isAuthenticated.value) {
      console.log('Token wygasł - wylogowanie')
      await logout()
      return
    }

    try {
      const response = await authAPI.me()
      user.value = response.data
      localStorage.setItem('user', JSON.stringify(user.value))
    } catch (err) {
      console.error('Fetch user failed:', err)
      // NIE wylogowuj natychmiast - może to być chwilowy problem z siecią
      // Token nadal może być ważny
      if (err.response?.status === 401) {
        // Tylko jeśli backend mówi że token jest nieważny
        await logout()
      }
    }
  }

  // Initialize on store creation
  if (token.value) {
    // Sprawdź czy token nie wygasł
    if (isAuthenticated.value) {
      // Spróbuj załadować użytkownika z localStorage najpierw
      const cachedUser = localStorage.getItem('user')
      if (cachedUser) {
        try {
          user.value = JSON.parse(cachedUser)
        } catch (e) {
          console.error('Failed to parse cached user:', e)
        }
      }
      // Potem pobierz świeże dane (nie blokuj jeśli się nie uda)
      fetchUser()
    } else {
      // Token wygasł - usuń go
      localStorage.removeItem('token')
      localStorage.removeItem('tokenExpiry')
      localStorage.removeItem('user')
    }
  }

  return {
    user,
    token,
    tokenExpiry,
    isAuthenticated,
    error,
    login,
    logout,
    fetchUser
  }
})

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api, { authAPI } from '../services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))
  const isAuthenticated = computed(() => !!token.value)
  const error = ref(null)

  async function login(credentials) {
    try {
      error.value = null
      const response = await authAPI.login(credentials)
      user.value = response.data.user
      token.value = response.data.token
      localStorage.setItem('token', token.value)
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
      localStorage.removeItem('token')
    }
  }

  async function fetchUser() {
    if (!token.value) {
      return
    }

    try {
      const response = await authAPI.me()
      user.value = response.data
    } catch (err) {
      console.error('Fetch user failed:', err)
      await logout()
    }
  }

  // Initialize on store creation
  if (token.value) {
    fetchUser()
  }

  return {
    user,
    token,
    isAuthenticated,
    error,
    login,
    logout,
    fetchUser
  }
})

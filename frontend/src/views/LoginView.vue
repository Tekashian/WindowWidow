<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="logo-container">
          <div class="logo-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
              <line x1="9" y1="3" x2="9" y2="21"/>
              <line x1="15" y1="3" x2="15" y2="21"/>
            </svg>
          </div>
          <h1>WindowWidow</h1>
        </div>
        <p class="subtitle">Production Management System</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label>Email Address</label>
          <input 
            v-model="credentials.email" 
            type="email" 
            class="form-control"
            placeholder="Enter your email"
            required
            autofocus
          />
        </div>

        <div class="form-group">
          <label>Password</label>
          <input 
            v-model="credentials.password" 
            type="password" 
            class="form-control"
            placeholder="Enter your password"
            required
          />
        </div>

        <div v-if="error" class="alert alert-error">
          {{ error }}
        </div>

        <button type="submit" class="btn btn-primary btn-block" :disabled="loading">
          <span v-if="loading">Authenticating...</span>
          <span v-else>Sign In</span>
        </button>
      </form>

      <div class="demo-credentials">
        <div class="demo-header">Test Accounts</div>
        <div class="demo-list">
          <div class="demo-item" @click="fillCredentials('admin@okna.pl', 'admin123')">
            <span class="demo-role">Admin</span>
            <span class="demo-email">admin@okna.pl</span>
            <span class="demo-pass">admin123</span>
          </div>
          <div class="demo-item" @click="fillCredentials('magazyn@okna.pl', 'magazyn123')">
            <span class="demo-role">Warehouse</span>
            <span class="demo-email">magazyn@okna.pl</span>
            <span class="demo-pass">magazyn123</span>
          </div>
          <div class="demo-item" @click="fillCredentials('produkcja@okna.pl', 'produkcja123')">
            <span class="demo-role">Production</span>
            <span class="demo-email">produkcja@okna.pl</span>
            <span class="demo-pass">produkcja123</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const credentials = ref({
  email: '',
  password: ''
})

const loading = ref(false)
const error = ref(null)

const fillCredentials = (email, password) => {
  credentials.value.email = email
  credentials.value.password = password
}

const handleLogin = async () => {
  loading.value = true
  error.value = null
  
  const result = await authStore.login(credentials.value)
  
  if (result.success) {
    router.push('/')
  } else {
    error.value = result.error || 'Invalid email or password'
  }
  
  loading.value = false
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--dark) 0%, var(--darker) 100%);
  padding: 2rem;
  position: relative;
  overflow: hidden;
}

.login-container::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(0, 245, 255, 0.1) 0%, transparent 70%);
  animation: rotate 20s linear infinite;
}

@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.login-card {
  max-width: 450px;
  width: 100%;
  background: white;
  border-radius: 20px;
  padding: 3rem 2.5rem;
  box-shadow: var(--shadow-xl);
  position: relative;
  z-index: 1;
  animation: slideUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(50px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.login-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.logo-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.logo-icon {
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--gradient-primary);
  border-radius: 15px;
  box-shadow: var(--shadow-glow);
}

.logo-icon svg {
  width: 32px;
  height: 32px;
  color: white;
}

.login-header h1 {
  font-size: 1.75rem;
  margin: 0;
  color: var(--gray-900);
  font-weight: 700;
}

.subtitle {
  color: var(--gray-500);
  font-size: 0.95rem;
  margin: 0;
  font-weight: 500;
}

.login-form {
  margin-bottom: 2rem;
}

.btn-block {
  width: 100%;
  padding: 1rem;
  font-size: 1rem;
  margin-top: 0.5rem;
}

.demo-credentials {
  padding: 1.5rem;
  background: var(--gray-50);
  border-radius: 12px;
  border: 1px solid var(--gray-200);
}

.demo-header {
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--gray-700);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 1rem;
}

.demo-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.demo-item {
  display: grid;
  grid-template-columns: 90px 1fr auto;
  gap: 0.75rem;
  padding: 0.75rem;
  cursor: pointer;
}

.demo-item:hover {
  border-color: var(--primary);
  box-shadow: var(--shadow-sm);
  transform: translateY(-2pxition-base);
}

.demo-item:hover {
  border-color: var(--primary);
  box-shadow: var(--shadow-sm);
}

.demo-role {
  font-weight: 700;
  color: var(--gray-800);
}

.demo-email {
  color: var(--gray-600);
  font-family: 'Courier New', monospace;
  font-size: 0.8rem;
}

.demo-pass {
  color: var(--gray-500);
  font-family: 'Courier New', monospace;
  font-size: 0.8rem;
}
</style>

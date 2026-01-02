<script setup>
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { computed } from 'vue'
import { useAuthStore } from './stores/auth'

const authStore = useAuthStore()
const router = useRouter()

const user = computed(() => authStore.user)

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <div id="app">
    <nav class="sidebar" v-if="user">
      <div class="sidebar-header">
        <div class="logo">
          <div class="logo-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
              <line x1="9" y1="3" x2="9" y2="21"/>
              <line x1="15" y1="3" x2="15" y2="21"/>
            </svg>
          </div>
          <span class="logo-text">Window Factory</span>
        </div>
        <div class="user-info" v-if="user">
          <div class="user-avatar">{{ user.name.charAt(0) }}</div>
          <div class="user-details">
            <div class="user-name">{{ user.name }}</div>
            <div class="user-role">{{ user.role }}</div>
          </div>
        </div>
      </div>

      <ul class="nav-menu">
        <li>
          <RouterLink to="/" class="nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
              <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
            <span>Dashboard</span>
          </RouterLink>
        </li>
        <li>
          <RouterLink to="/production-orders" class="nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
            <span>Production Orders</span>
          </RouterLink>
        </li>
        <li>
          <RouterLink to="/materials" class="nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
              <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
            </svg>
            <span>Warehouse</span>
          </RouterLink>
        </li>
        <li>
          <RouterLink to="/windows" class="nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
              <line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/>
              <line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/>
            </svg>
            <span>Windows</span>
          </RouterLink>
        </li>
        <li>
          <RouterLink to="/profiles" class="nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
            </svg>
            <span>Profiles</span>
          </RouterLink>
        </li>
        <li>
          <RouterLink to="/glasses" class="nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <circle cx="12" cy="12" r="6"/>
              <circle cx="12" cy="12" r="2"/>
            </svg>
            <span>Glass Types</span>
          </RouterLink>
        </li>
        <li>
          <RouterLink to="/orders" class="nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
              <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 12h6"/><path d="M9 16h6"/>
            </svg>
            <span>Orders</span>
          </RouterLink>
        </li>
      </ul>

      <div class="sidebar-footer" v-if="user">
        <button @click="handleLogout" class="btn btn-secondary btn-logout">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          <span>Sign Out</span>
        </button>
      </div>
    </nav>

    <main class="main-content" :class="{ 'full-width': !user }">
      <div class="content-wrapper">
        <RouterView v-slot="{ Component }">
          <Transition name="fade" mode="out-in">
            <component :is="Component" />
          </Transition>
        </RouterView>
      </div>

      <footer class="footer" v-if="user">
        <p>&copy; 2026 Window Factory - Production Management System</p>
      </footer>
    </main>
  </div>
</template>

<style scoped>
#app {
  display: flex;
  min-height: 100vh;
}

.sidebar {
  width: 280px;
  background: var(--gradient-dark);
  color: white;
  display: flex;
  flex-direction: column;
  box-shadow: var(--shadow-xl);
  position: fixed;
  height: 100vh;
  overflow-y: auto;
  z-index: 100;
}

.sidebar-header {
  padding: 2rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.logo-icon {
  width: 45px;
  height: 45px;
  background: var(--gradient-primary);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: var(--shadow-glow);
}

.logo-icon svg {
  width: 24px;
  height: 24px;
  color: white;
}

.logo-text {
  font-size: 1.25rem;
  font-weight: 700;
  color: white;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.user-avatar {
  width: 40px;
  height: 40px;
  background: var(--gradient-primary);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.1rem;
  color: white;
}

.user-details {
  flex: 1;
}

.user-name {
  font-weight: 600;
  font-size: 0.95rem;
  color: white;
}

.user-role {
  font-size: 0.8rem;
  color: var(--gray-400);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.nav-menu {
  list-style: none;
  padding: 1rem 0;
  flex: 1;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  color: var(--gray-300);
  text-decoration: none;
  transition: all var(--transition-base);
  border-left: 3px solid transparent;
  font-weight: 500;
}

.nav-link svg {
  width: 22px;
  height: 22px;
  flex-shrink: 0;
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.05);
  color: white;
  border-left-color: var(--primary);
}

.nav-link.router-link-active {
  background: rgba(0, 245, 255, 0.1);
  color: white;
  border-left-color: var(--primary);
  font-weight: 600;
  box-shadow: inset 0 0 20px rgba(0, 245, 255, 0.1);
}

.sidebar-footer {
  padding: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-logout {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-logout:hover {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.3);
}

.btn-logout svg {
  width: 18px;
  height: 18px;
}

.main-content {
  flex: 1;
  margin-left: 280px;
  display: flex;
  flex-direction: column;
  background: var(--gray-50);
}

.main-content.full-width {
  margin-left: 0;
}

.content-wrapper {
  flex: 1;
  padding: 2rem;
  max-width: 1600px;
  width: 100%;
  margin: 0 auto;
}

.footer {
  background: white;
  padding: 1.5rem;
  text-align: center;
  border-top: 1px solid var(--gray-200);
  color: var(--gray-500);
  font-size: 0.875rem;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity var(--transition-base);
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (max-width: 768px) {
  .sidebar {
    width: 70px;
  }

  .sidebar-header {
    padding: 1.5rem 0.5rem;
  }

  .logo-text,
  .user-info {
    display: none;
  }

  .logo {
    justify-content: center;
  }

  .nav-link span {
    display: none;
  }

  .nav-link {
    justify-content: center;
    padding: 1rem 0.5rem;
  }

  .btn-logout span {
    display: none;
  }

  .main-content {
    margin-left: 70px;
  }
}
</style>

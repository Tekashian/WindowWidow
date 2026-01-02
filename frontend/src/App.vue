<script setup>
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { computed, ref } from 'vue'
import { useAuthStore } from './stores/auth'
import NotificationCenter from './components/NotificationCenter.vue'
import ToastContainer from './components/ToastContainer.vue'
import ConfirmDialog from './components/ConfirmDialog.vue'

const authStore = useAuthStore()
const router = useRouter()
const confirmDialogRef = ref(null)

const user = computed(() => authStore.user)
const mobileMenuOpen = ref(false)

// Permission checks
const canAccessProduction = computed(() => authStore.canAccessProduction())
const canAccessWarehouse = computed(() => authStore.canAccessWarehouse())
const canAccessAdmin = computed(() => authStore.canAccessAdmin())

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

const closeMobileMenu = () => {
  mobileMenuOpen.value = false
}
</script>

<template>
  <div id="app">
    <!-- Mobile Menu Toggle -->
    <button v-if="user" @click="mobileMenuOpen = !mobileMenuOpen" class="mobile-menu-toggle">
      <svg v-if="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
      </svg>
      <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </button>

    <!-- Mobile Overlay -->
    <div v-if="mobileMenuOpen" @click="closeMobileMenu" class="mobile-overlay"></div>

    <nav class="sidebar" v-if="user" :class="{ 'mobile-open': mobileMenuOpen }">
      <div class="sidebar-header">
        <div class="logo">
          <div class="logo-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
              <line x1="9" y1="3" x2="9" y2="21"/>
              <line x1="15" y1="3" x2="15" y2="21"/>
            </svg>
          </div>
          <span class="logo-text">WindowWidow</span>
        </div>
        <div class="user-info" v-if="user">
          <div class="flex items-center gap-3">
            <NotificationCenter />
            <div class="user-avatar">{{ user.name.charAt(0) }}</div>
          </div>
          <div class="user-details">
            <div class="user-name">{{ user.name }}</div>
            <div class="user-role">{{ user.role }}</div>
          </div>
        </div>
      </div>

      <ul class="nav-menu">
        <li>
          <RouterLink to="/" class="nav-link" @click="closeMobileMenu">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
              <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
            <span>Dashboard</span>
          </RouterLink>
        </li>
        
        <!-- Production Panel -->
        <template v-if="canAccessProduction">
          <li class="nav-section-title">🏭 Produkcja</li>
          <li>
            <RouterLink to="/production" class="nav-link" @click="closeMobileMenu">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
              </svg>
              <span>Dashboard Produkcji</span>
            </RouterLink>
          </li>
          <li>
            <RouterLink to="/production/orders" class="nav-link" @click="closeMobileMenu">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
              </svg>
              <span>Zlecenia</span>
            </RouterLink>
          </li>
          <li>
            <RouterLink to="/production/issues" class="nav-link" @click="closeMobileMenu">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
              <span>Problemy</span>
            </RouterLink>
          </li>
        </template>
        
        <!-- Warehouse Panel -->
        <template v-if="canAccessWarehouse">
          <li class="nav-section-title">📦 Magazyn</li>
          <li>
            <RouterLink to="/warehouse" class="nav-link" @click="closeMobileMenu">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
              </svg>
              <span>Dostawy</span>
            </RouterLink>
          </li>
        </template>
        <li v-if="canAccessWarehouse">
          <RouterLink to="/materials" class="nav-link" @click="closeMobileMenu">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
              <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
            </svg>
            <span>Zapasy</span>
          </RouterLink>
        </li>
        <li>
          <RouterLink to="/materials" class="nav-link" @click="closeMobileMenu">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
              <line x1="3" y1="9" x2="21" y2="9"/>
              <line x1="9" y1="21" x2="9" y2="9"/>
            </svg>
            <span>Materiały</span>
          </RouterLink>
        </li>
        
        <li class="nav-section-title">🪟 Katalog</li>
        <li>
          <RouterLink to="/windows" class="nav-link" @click="closeMobileMenu">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
              <line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/>
              <line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/>
            </svg>
            <span>Okna</span>
          </RouterLink>
        </li>
        <li>
          <RouterLink to="/profiles" class="nav-link" @click="closeMobileMenu">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
            </svg>
            <span>Profile</span>
          </RouterLink>
        </li>
        <li>
          <RouterLink to="/glasses" class="nav-link" @click="closeMobileMenu">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <circle cx="12" cy="12" r="6"/>
              <circle cx="12" cy="12" r="2"/>
            </svg>
            <span>Szkła</span>
          </RouterLink>
        </li>
        
        <li class="nav-section-title">📋 Zamówienia</li>
        <li>
          <RouterLink to="/orders" class="nav-link" @click="closeMobileMenu">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
              <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 12h6"/><path d="M9 16h6"/>
            </svg>
            <span>Zamówienia</span>
          </RouterLink>
        </li>
        
        <!-- Admin Panel -->
        <template v-if="canAccessAdmin">
          <li class="nav-section-title">⚙️ Administracja</li>
          <li>
            <RouterLink to="/admin" class="nav-link" @click="closeMobileMenu">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/><path d="M12 1v6m0 6v6m8.66-14.66l-4.24 4.24m-4.24 4.24l-4.24 4.24m16.97-1.41l-6-1.73m-6-1.73l-6-1.73m1.41 16.97l1.73-6m1.73-6l1.73-6"/>
              </svg>
              <span>Panel Admina</span>
            </RouterLink>
          </li>
          <li>
            <RouterLink to="/admin/windows" class="nav-link" @click="closeMobileMenu">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/>
                <line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/>
              </svg>
              <span>Zarządzanie oknami</span>
            </RouterLink>
          </li>
        </template>
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
        <div class="copyright">
          <p>&copy; 2026 WindowWidow - Production Management System</p>
          <p class="author">Created by Jakub Łącki | All Rights Reserved</p>
        </div>
      </footer>
    </main>

    <!-- Global Components -->
    <ToastContainer />
    <ConfirmDialog ref="confirmDialogRef" />
  </div>
</template>

<style scoped>
#app {
  display: flex;
  min-height: 100vh;
}

/* Mobile Menu Toggle Button */
.mobile-menu-toggle {
  display: none;
  position: fixed;
  top: 1rem;
  left: 1rem;
  z-index: 1001;
  width: 48px;
  height: 48px;
  border: none;
  border-radius: 12px;
  background: var(--gradient-primary);
  color: white;
  cursor: pointer;
  box-shadow: var(--shadow-lg);
  transition: all var(--transition-base);
  align-items: center;
  justify-content: center;
}

.mobile-menu-toggle:active {
  transform: scale(0.95);
}

.mobile-menu-toggle svg {
  width: 24px;
  height: 24px;
}

/* Mobile Overlay */
.mobile-overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
  backdrop-filter: blur(4px);
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
  transition: transform var(--transition-base);
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
  flex-shrink: 0;
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
  flex-shrink: 0;
}

.user-details {
  flex: 1;
  min-width: 0;
}

.user-name {
  font-weight: 600;
  font-size: 0.95rem;
  color: white;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
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

.nav-section-title {
  padding: 1.5rem 1.5rem 0.5rem 1.5rem;
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--gray-500);
  text-transform: uppercase;
  letter-spacing: 0.1em;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  margin-top: 1rem;
}

.nav-section-title:first-child {
  margin-top: 0;
  border-top: none;
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
  min-height: 48px;
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
  min-height: 48px;
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
  min-width: 0;
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

.copyright {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.copyright p {
  margin: 0;
}

.author {
  font-size: 0.8rem;
  color: var(--gray-400);
  font-weight: 600;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity var(--transition-base);
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Tablet Styles */
@media (max-width: 1024px) {
  .content-wrapper {
    padding: 1.5rem;
  }
}

/* Mobile Styles */
@media (max-width: 768px) {
  .mobile-menu-toggle {
    display: flex;
  }

  .mobile-overlay {
    display: block;
  }

  .sidebar {
    transform: translateX(-100%);
    width: 280px;
    z-index: 1000;
  }

  .sidebar.mobile-open {
    transform: translateX(0);
  }

  .main-content {
    margin-left: 0;
    padding-top: 72px;
  }

  .content-wrapper {
    padding: 1rem;
  }

  .footer {
    padding: 1rem;
    font-size: 0.75rem;
  }

  .author {
    font-size: 0.7rem;
  }
}

/* Small Mobile Styles */
@media (max-width: 480px) {
  .mobile-menu-toggle {
    width: 44px;
    height: 44px;
  }

  .sidebar {
    width: 260px;
  }

  .sidebar-header {
    padding: 1.5rem 1rem;
  }

  .logo {
    gap: 0.75rem;
    margin-bottom: 1rem;
  }

  .logo-icon {
    width: 40px;
    height: 40px;
  }

  .logo-text {
    font-size: 1.1rem;
  }

  .user-info {
    padding: 0.75rem;
  }

  .user-avatar {
    width: 36px;
    height: 36px;
    font-size: 1rem;
  }

  .nav-link {
    padding: 0.875rem 1rem;
    font-size: 0.9rem;
  }

  .content-wrapper {
    padding: 0.75rem;
  }
}
</style>

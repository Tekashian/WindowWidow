import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { requiresAuth: true }
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue')
    },
    {
      path: '/production-orders',
      name: 'production-orders',
      component: () => import('../views/ProductionOrdersView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/materials',
      name: 'materials',
      component: () => import('../views/MaterialsView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/windows',
      name: 'windows',
      component: () => import('../views/WindowsView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/profiles',
      name: 'profiles',
      component: () => import('../views/ProfilesView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/glasses',
      name: 'glasses',
      component: () => import('../views/GlassesView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/orders',
      name: 'orders',
      component: () => import('../views/OrdersView.vue'),
      meta: { requiresAuth: true }
    },
    // Production Panel Routes
    {
      path: '/production',
      name: 'production-dashboard',
      component: () => import('../views/production/ProductionDashboard.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/production/orders',
      name: 'production-orders-list',
      component: () => import('../views/production/ProductionOrdersList.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/production/orders/:id',
      name: 'production-order-details',
      component: () => import('../views/production/ProductionOrderDetails.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/production/issues',
      name: 'production-issues',
      component: () => import('../views/production/ProductionIssues.vue'),
      meta: { requiresAuth: true }
    },
    // Warehouse Panel Routes
    {
      path: '/warehouse',
      name: 'warehouse-dashboard',
      component: () => import('../views/warehouse/WarehouseDashboard.vue'),
      meta: { requiresAuth: true }
    }
  ]
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.path === '/login' && authStore.isAuthenticated) {
    next('/')
  } else {
    next()
  }
})

export default router

import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor
api.interceptors.request.use(
  config => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  error => {
    return Promise.reject(error)
  }
)

// Response interceptor
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// Auth API
export const authAPI = {
  login: (credentials) => api.post('/login', credentials),
  logout: () => api.post('/logout'),
  me: () => api.get('/me')
}

// Dashboard API
export const dashboardAPI = {
  getStatistics: () => api.get('/dashboard'),
  exportMaterials: () => api.get('/dashboard/export-materials', { responseType: 'blob' })
}

// Windows API
export const windowsAPI = {
  getAll: () => api.get('/windows'),
  getOne: (id) => api.get(`/windows/${id}`),
  create: (data) => api.post('/windows', data),
  update: (id, data) => api.put(`/windows/${id}`, data),
  delete: (id) => api.delete(`/windows/${id}`)
}

// Profiles API
export const profilesAPI = {
  getAll: () => api.get('/profiles'),
  getOne: (id) => api.get(`/profiles/${id}`),
  create: (data) => api.post('/profiles', data),
  update: (id, data) => api.put(`/profiles/${id}`, data),
  delete: (id) => api.delete(`/profiles/${id}`)
}

// Glasses API
export const glassesAPI = {
  getAll: () => api.get('/glasses'),
  getOne: (id) => api.get(`/glasses/${id}`),
  create: (data) => api.post('/glasses', data),
  update: (id, data) => api.put(`/glasses/${id}`, data),
  delete: (id) => api.delete(`/glasses/${id}`)
}

// Orders API
export const ordersAPI = {
  getAll: () => api.get('/orders'),
  getOne: (id) => api.get(`/orders/${id}`),
  create: (data) => api.post('/orders', data),
  update: (id, data) => api.put(`/orders/${id}`, data),
  updateStatus: (id, status) => api.post(`/orders/${id}/update-status`, { status }),
  delete: (id) => api.delete(`/orders/${id}`)
}

// Materials API
export const materialsAPI = {
  getAll: () => api.get('/materials'),
  getOne: (id) => api.get(`/materials/${id}`),
  create: (data) => api.post('/materials', data),
  update: (id, data) => api.put(`/materials/${id}`, data),
  delete: (id) => api.delete(`/materials/${id}`),
  addStock: (id, data) => api.post(`/materials/${id}/add-stock`, data),
  removeStock: (id, data) => api.post(`/materials/${id}/remove-stock`, data),
  getMovements: (id) => api.get(`/materials/${id}/movements`),
  getLowStock: () => api.get('/low-stock')
}

// Production Orders API
export const productionOrdersAPI = {
  getAll: () => api.get('/production-orders'),
  getOne: (id) => api.get(`/production-orders/${id}`),
  create: (data) => api.post('/production-orders', data),
  update: (id, data) => api.put(`/production-orders/${id}`, data),
  delete: (id) => api.delete(`/production-orders/${id}`),
  start: (id) => api.post(`/production-orders/${id}/start`),
  complete: (id) => api.post(`/production-orders/${id}/complete`),
  cancel: (id) => api.post(`/production-orders/${id}/cancel`)
}

export default api

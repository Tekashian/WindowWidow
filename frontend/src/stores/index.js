import { defineStore } from 'pinia'
import { ref } from 'vue'
import { materialsAPI, productionOrdersAPI, dashboardAPI } from '../services/api'

export const useDashboardStore = defineStore('dashboard', () => {
  const statistics = ref(null)
  const loading = ref(false)
  const error = ref(null)

  async function fetchStatistics() {
    loading.value = true
    error.value = null
    try {
      const response = await dashboardAPI.getStatistics()
      statistics.value = response.data
    } catch (err) {
      error.value = err.message
    } finally {
      loading.value = false
    }
  }

  return {
    statistics,
    loading,
    error,
    fetchStatistics
  }
})

export const useMaterialStore = defineStore('materials', () => {
  const materials = ref([])
  const lowStockMaterials = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function fetchMaterials() {
    loading.value = true
    error.value = null
    try {
      const response = await materialsAPI.getAll()
      materials.value = response.data
    } catch (err) {
      error.value = err.message
    } finally {
      loading.value = false
    }
  }

  async function fetchLowStock() {
    try {
      const response = await materialsAPI.getLowStock()
      lowStockMaterials.value = response.data
    } catch (err) {
      console.error('Failed to fetch low stock:', err)
    }
  }

  async function addStock(materialId, quantity, reason) {
    try {
      await materialsAPI.addStock(materialId, { quantity, reason })
      await fetchMaterials()
      return true
    } catch (err) {
      error.value = err.message
      return false
    }
  }

  async function removeStock(materialId, quantity, reason) {
    try {
      await materialsAPI.removeStock(materialId, { quantity, reason })
      await fetchMaterials()
      return true
    } catch (err) {
      error.value = err.message
      return false
    }
  }

  return {
    materials,
    lowStockMaterials,
    loading,
    error,
    fetchMaterials,
    fetchLowStock,
    addStock,
    removeStock
  }
})

export const useProductionOrderStore = defineStore('productionOrders', () => {
  const orders = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function fetchOrders() {
    loading.value = true
    error.value = null
    try {
      const response = await productionOrdersAPI.getAll()
      orders.value = response.data
    } catch (err) {
      error.value = err.message
    } finally {
      loading.value = false
    }
  }

  async function createOrder(data) {
    try {
      const response = await productionOrdersAPI.create(data)
      await fetchOrders()
      return response.data
    } catch (err) {
      error.value = err.response?.data?.error || err.message
      throw err
    }
  }

  async function startOrder(orderId) {
    try {
      await productionOrdersAPI.start(orderId)
      await fetchOrders()
      return true
    } catch (err) {
      error.value = err.response?.data?.error || err.message
      return false
    }
  }

  async function completeOrder(orderId) {
    try {
      await productionOrdersAPI.complete(orderId)
      await fetchOrders()
      return true
    } catch (err) {
      error.value = err.response?.data?.error || err.message
      return false
    }
  }

  return {
    orders,
    loading,
    error,
    fetchOrders,
    createOrder,
    startOrder,
    completeOrder
  }
})

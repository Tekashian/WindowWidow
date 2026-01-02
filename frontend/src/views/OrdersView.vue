<template>
  <div class="orders-view">
    <div class="header">
      <h1>Zamówienia</h1>
      <button @click="showForm = true" class="btn btn-primary">+ Nowe zamówienie</button>
    </div>

    <div v-if="loading" class="loading">Ładowanie danych...</div>
    <div v-if="error" class="error">{{ error }}</div>

    <div v-if="!loading && !error" class="orders-list">
      <div v-for="order in orders" :key="order.id" class="card order-card">
        <div class="order-header">
          <h3>{{ order.order_number }}</h3>
          <span :class="['status', `status-${order.status}`]">{{ getStatusLabel(order.status) }}</span>
        </div>
        <p><strong>Klient:</strong> {{ order.customer_name }}</p>
        <p><strong>Email:</strong> {{ order.customer_email }}</p>
        <p><strong>Telefon:</strong> {{ order.customer_phone }}</p>
        <p><strong>Adres dostawy:</strong> {{ order.delivery_address }}</p>
        <p class="total"><strong>Wartość:</strong> {{ order.total_price }} zł</p>
        <p><strong>Data zamówienia:</strong> {{ formatDate(order.ordered_at) }}</p>
        <div class="actions">
          <button @click="viewOrder(order)" class="btn btn-primary">Szczegóły</button>
          <button @click="changeStatus(order)" class="btn btn-secondary">Zmień status</button>
          <button @click="deleteOrder(order.id)" class="btn btn-danger">Usuń</button>
        </div>
      </div>
    </div>

    <div v-if="showForm" class="modal">
      <div class="modal-content card">
        <h2>Nowe zamówienie</h2>
        <p>Funkcjonalność w trakcie implementacji</p>
        <button @click="closeForm" class="btn btn-secondary">Zamknij</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ordersAPI } from '../services/api'

const orders = ref([])
const loading = ref(false)
const error = ref(null)
const showForm = ref(false)

const loadOrders = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await ordersAPI.getAll()
    orders.value = response.data
  } catch (err) {
    error.value = 'Nie udało się załadować danych: ' + err.message
  } finally {
    loading.value = false
  }
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Oczekujące',
    processing: 'W realizacji',
    completed: 'Zakończone',
    cancelled: 'Anulowane'
  }
  return labels[status] || status
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pl-PL')
}

const viewOrder = (order) => {
  alert('Szczegóły zamówienia: ' + JSON.stringify(order, null, 2))
}

const changeStatus = async (order) => {
  const statuses = ['pending', 'processing', 'completed', 'cancelled']
  const currentIndex = statuses.indexOf(order.status)
  const nextStatus = statuses[(currentIndex + 1) % statuses.length]
  
  if (confirm(`Zmienić status na: ${getStatusLabel(nextStatus)}?`)) {
    try {
      await ordersAPI.updateStatus(order.id, nextStatus)
      loadOrders()
    } catch (err) {
      error.value = 'Nie udało się zmienić statusu: ' + err.message
    }
  }
}

const deleteOrder = async (id) => {
  if (confirm('Czy na pewno usunąć to zamówienie?')) {
    try {
      await ordersAPI.delete(id)
      loadOrders()
    } catch (err) {
      error.value = 'Nie udało się usunąć: ' + err.message
    }
  }
}

const closeForm = () => {
  showForm.value = false
}

onMounted(() => {
  loadOrders()
})
</script>

<style scoped>
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.orders-list {
  display: grid;
  gap: 1.5rem;
}

.order-card {
  position: relative;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.status {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
}

.status-pending {
  background-color: #ffc107;
  color: #000;
}

.status-processing {
  background-color: #2196f3;
  color: white;
}

.status-completed {
  background-color: #4caf50;
  color: white;
}

.status-cancelled {
  background-color: #f44336;
  color: white;
}

.total {
  font-size: 1.2rem;
  color: #42b983;
  margin: 1rem 0;
}

.actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  max-width: 500px;
  width: 90%;
}
</style>

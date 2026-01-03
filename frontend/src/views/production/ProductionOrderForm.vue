<template>
  <div class="order-form-container">
    <div class="form-header">
      <button @click="$router.back()" class="back-button">
        ← Powrót
      </button>
      <h1>Nowe Zlecenie Produkcyjne</h1>
    </div>

    <div v-if="error" class="error-alert">
      {{ error }}
    </div>

    <form @submit.prevent="handleSubmit" class="order-form">
      <!-- Customer Information -->
      <section class="form-section">
        <h2>Informacje o Kliencie</h2>
        <div class="form-grid">
          <div class="form-group">
            <label for="customer_name">Imię i Nazwisko / Firma *</label>
            <input
              id="customer_name"
              v-model="form.customer_name"
              type="text"
              required
              placeholder="Jan Kowalski"
            />
          </div>

          <div class="form-group">
            <label for="customer_phone">Telefon *</label>
            <input
              id="customer_phone"
              v-model="form.customer_phone"
              type="tel"
              required
              placeholder="+48 123 456 789"
            />
          </div>

          <div class="form-group">
            <label for="customer_email">Email</label>
            <input
              id="customer_email"
              v-model="form.customer_email"
              type="email"
              placeholder="klient@example.com"
            />
          </div>
        </div>
      </section>

      <!-- Delivery Address -->
      <section class="form-section">
        <h2>Adres Dostawy</h2>
        <div class="form-grid">
          <div class="form-group full-width">
            <label for="delivery_address">Ulica i Numer *</label>
            <input
              id="delivery_address"
              v-model="form.delivery_address"
              type="text"
              required
              placeholder="ul. Przykładowa 123"
            />
          </div>

          <div class="form-group">
            <label for="delivery_city">Miasto *</label>
            <input
              id="delivery_city"
              v-model="form.delivery_city"
              type="text"
              required
              placeholder="Warszawa"
            />
          </div>

          <div class="form-group">
            <label for="delivery_postal_code">Kod Pocztowy *</label>
            <input
              id="delivery_postal_code"
              v-model="form.delivery_postal_code"
              type="text"
              required
              placeholder="00-000"
              pattern="[0-9]{2}-[0-9]{3}"
            />
          </div>

          <div class="form-group full-width">
            <label for="delivery_notes">Uwagi do dostawy</label>
            <textarea
              id="delivery_notes"
              v-model="form.delivery_notes"
              rows="2"
              placeholder="Dodatkowe informacje dla kuriera..."
            ></textarea>
          </div>
        </div>
      </section>

      <!-- Product Details -->
      <section class="form-section">
        <h2>Szczegóły Produktu</h2>
        <div class="form-grid">
          <div class="form-group">
            <label for="product_type">Typ Produktu *</label>
            <select id="product_type" v-model="form.product_type" required>
              <option value="">Wybierz...</option>
              <option value="Okno">Okno</option>
              <option value="Drzwi">Drzwi</option>
              <option value="Panel szklany">Panel szklany</option>
              <option value="Balkon">Balkon</option>
              <option value="Inna stolarka">Inna stolarka</option>
            </select>
          </div>

          <div class="form-group">
            <label for="quantity">Ilość *</label>
            <input
              id="quantity"
              v-model.number="form.quantity"
              type="number"
              min="1"
              required
              placeholder="1"
            />
          </div>

          <div class="form-group full-width">
            <label for="product_description">Opis Produktu *</label>
            <textarea
              id="product_description"
              v-model="form.product_description"
              rows="3"
              required
              placeholder="Szczegółowy opis produktu..."
            ></textarea>
          </div>

          <!-- Product Specifications -->
          <div class="form-group">
            <label for="width">Szerokość (cm)</label>
            <input
              id="width"
              v-model.number="specifications.width"
              type="number"
              placeholder="120"
            />
          </div>

          <div class="form-group">
            <label for="height">Wysokość (cm)</label>
            <input
              id="height"
              v-model.number="specifications.height"
              type="number"
              placeholder="150"
            />
          </div>

          <div class="form-group">
            <label for="color">Kolor</label>
            <input
              id="color"
              v-model="specifications.color"
              type="text"
              placeholder="Biały"
            />
          </div>

          <div class="form-group">
            <label for="material">Materiał</label>
            <input
              id="material"
              v-model="specifications.material"
              type="text"
              placeholder="PVC"
            />
          </div>
        </div>
      </section>

      <!-- Order Settings -->
      <section class="form-section">
        <h2>Ustawienia Zlecenia</h2>
        <div class="form-grid">
          <div class="form-group">
            <label for="priority">Priorytet *</label>
            <select id="priority" v-model="form.priority" required>
              <option value="low">Niski</option>
              <option value="normal">Normalny</option>
              <option value="high">Wysoki</option>
              <option value="urgent">Pilne</option>
            </select>
          </div>

          <div class="form-group">
            <label for="source_type">Źródło *</label>
            <select id="source_type" v-model="form.source_type" required>
              <option value="customer_order">Zamówienie klienta</option>
              <option value="stock_replenishment">Uzupełnienie magazynu</option>
            </select>
          </div>

          <div class="form-group full-width">
            <label for="notes">Uwagi</label>
            <textarea
              id="notes"
              v-model="form.notes"
              rows="3"
              placeholder="Dodatkowe informacje dla zespołu produkcji..."
            ></textarea>
          </div>
        </div>
      </section>

      <!-- Form Actions -->
      <div class="form-actions">
        <button type="button" @click="$router.back()" class="btn-cancel">
          Anuluj
        </button>
        <button type="submit" :disabled="loading" class="btn-submit">
          {{ loading ? 'Tworzenie...' : 'Utwórz Zlecenie' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useProductionStore } from '../../stores/productionStore'

const router = useRouter()
const productionStore = useProductionStore()

const loading = ref(false)
const error = ref(null)

const form = reactive({
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  delivery_address: '',
  delivery_city: '',
  delivery_postal_code: '',
  delivery_notes: '',
  product_type: '',
  product_description: '',
  quantity: 1,
  priority: 'normal',
  source_type: 'customer_order',
  notes: ''
})

const specifications = reactive({
  width: null,
  height: null,
  color: '',
  material: ''
})

const handleSubmit = async () => {
  error.value = null
  loading.value = true

  try {
    // Build specifications object (only non-empty values)
    const productSpecs = {}
    if (specifications.width) productSpecs.width = specifications.width
    if (specifications.height) productSpecs.height = specifications.height
    if (specifications.color) productSpecs.color = specifications.color
    if (specifications.material) productSpecs.material = specifications.material

    const orderData = {
      ...form,
      product_specifications: Object.keys(productSpecs).length > 0 ? productSpecs : null
    }

    await productionStore.createOrder(orderData)
    
    // Redirect to orders list
    router.push({ name: 'production-orders-list' })
  } catch (err) {
    error.value = err.response?.data?.message || 'Nie udało się utworzyć zlecenia'
    console.error('Failed to create order:', err)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.order-form-container {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.form-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.back-button {
  background: var(--darker);
  color: var(--primary);
  border: 1px solid rgba(0, 245, 255, 0.3);
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s;
}

.back-button:hover {
  background: rgba(0, 245, 255, 0.1);
  border-color: var(--primary);
}

.form-header h1 {
  color: var(--primary);
  margin: 0;
  font-size: 2rem;
}

.error-alert {
  background: rgba(255, 68, 68, 0.1);
  border: 1px solid #ff4444;
  color: #ff6b6b;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 2rem;
}

.order-form {
  background: var(--darker);
  border: 1px solid rgba(0, 245, 255, 0.2);
  border-radius: 12px;
  padding: 2rem;
}

.form-section {
  margin-bottom: 2.5rem;
}

.form-section:last-of-type {
  margin-bottom: 2rem;
}

.form-section h2 {
  color: var(--primary);
  font-size: 1.5rem;
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid rgba(0, 245, 255, 0.2);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  color: rgba(255, 255, 255, 0.9);
  font-weight: 500;
  font-size: 0.9rem;
}

.form-group input,
.form-group select,
.form-group textarea {
  background: var(--dark);
  border: 1px solid rgba(0, 245, 255, 0.2);
  border-radius: 8px;
  padding: 0.75rem;
  color: white;
  font-family: inherit;
  transition: all 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 2px rgba(0, 245, 255, 0.1);
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.form-group select {
  cursor: pointer;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding-top: 2rem;
  border-top: 1px solid rgba(0, 245, 255, 0.2);
}

.btn-cancel {
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 0.75rem 2rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s;
}

.btn-cancel:hover {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.5);
}

.btn-submit {
  background: linear-gradient(135deg, var(--primary), #00d4ff);
  border: none;
  color: var(--darker);
  padding: 0.75rem 2rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(0, 245, 255, 0.3);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .order-form-container {
    padding: 1rem;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column-reverse;
  }

  .btn-cancel,
  .btn-submit {
    width: 100%;
  }
}
</style>

<template>
  <div class="order-form-container">
    <div class="form-header">
      <button @click="$router.back()" class="back-button">
        ← Powrót
      </button>
      <h1>Nowe Zlecenie Produkcyjne</h1>
    </div>

    <div v-if="loading" class="loading-alert">
      Ładowanie danych...
    </div>

    <div v-if="error" class="error-alert">
      {{ error }}
    </div>

    <form @submit.prevent="handleSubmit" class="order-form">
      <!-- Product Selection -->
      <section class="form-section">
        <h2>Wybór Produktu</h2>
        <div class="form-grid">
          <div class="form-group">
            <label for="product_id">Produkt *</label>
            <select
              id="product_id"
              v-model="form.product_id"
              required
              @change="onProductChange"
            >
              <option value="">Wybierz produkt...</option>
              <option
                v-for="product in products"
                :key="product.id"
                :value="product.id"
              >
                {{ product.name }} ({{ product.code }})
              </option>
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
        </div>

        <div v-if="selectedProduct" class="product-details">
          <h3>Szczegóły produktu:</h3>
          <p><strong>Typ:</strong> {{ selectedProduct.type }}</p>
          <p><strong>Opis:</strong> {{ selectedProduct.description }}</p>
          <p><strong>Szacowany czas produkcji:</strong> {{ selectedProduct.estimated_production_days }} dni</p>
          <div v-if="selectedProduct.default_specifications">
            <p><strong>Domyślne specyfikacje:</strong></p>
            <pre>{{ JSON.stringify(selectedProduct.default_specifications, null, 2) }}</pre>
          </div>
        </div>
      </section>

      <!-- Customer Information (Pre-filled with company data) -->
      <section class="form-section">
        <h2>Zamawiający (dane do faktury)</h2>
        <div class="info-box">
          <p><strong>Firma:</strong> {{ form.customer_name }}</p>
          <p><strong>Telefon:</strong> {{ form.customer_phone }}</p>
          <p><strong>Email:</strong> {{ form.customer_email }}</p>
        </div>
        <p class="help-text">Dane firmy (automatycznie wypełnione)</p>
      </section>

      <!-- Delivery Address (Pre-filled with warehouse) -->
      <section class="form-section">
        <h2>Miejsce Dostawy</h2>
        <div class="info-box">
          <p><strong>Adres:</strong> {{ form.delivery_address }}</p>
          <p><strong>Miasto:</strong> {{ form.delivery_city }}</p>
          <p><strong>Kod pocztowy:</strong> {{ form.delivery_postal_code }}</p>
        </div>
        <p class="help-text">Magazyn (automatycznie wypełniony)</p>
        
        <div class="form-group full-width">
          <label for="delivery_notes">Uwagi do dostawy</label>
          <textarea
            id="delivery_notes"
            v-model="form.delivery_notes"
            rows="2"
            placeholder="Dodatkowe informacje dotyczące dostawy"
          ></textarea>
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
            <label for="source_type">Źródło Zlecenia *</label>
            <select id="source_type" v-model="form.source_type" required>
              <option value="customer_order">Zamówienie klienta</option>
              <option value="stock_replenishment">Uzupełnienie magazynu</option>
            </select>
          </div>

          <div class="form-group full-width">
            <label for="notes">Uwagi do zlecenia</label>
            <textarea
              id="notes"
              v-model="form.notes"
              rows="3"
              placeholder="Dodatkowe informacje..."
            ></textarea>
          </div>
        </div>
      </section>
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
        <button type="submit" :disabled="loading || !form.product_id" class="btn-submit">
          {{ loading ? 'Tworzenie...' : 'Utwórz Zlecenie' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { productionApi } from '../../services/productionApi'
import { useProductionStore } from '../../stores/productionStore'

const router = useRouter()
const productionStore = useProductionStore()

const loading = ref(false)
const error = ref(null)
const products = ref([])
const companySettings = ref(null)

const form = reactive({
  product_id: '',
  quantity: 1,
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  delivery_address: '',
  delivery_city: '',
  delivery_postal_code: '',
  delivery_notes: '',
  priority: 'normal',
  source_type: 'customer_order',
  notes: ''
})

// Get selected product details
const selectedProduct = computed(() => {
  if (!form.product_id) return null
  return products.value.find(p => p.id === form.product_id)
})

// Load products and company settings on mount
onMounted(async () => {
  loading.value = true
  try {
    const [productsRes, settingsRes] = await Promise.all([
      productionApi.getProducts(),
      productionApi.getCompanySettings()
    ])
    
    products.value = productsRes.data.data || []
    companySettings.value = settingsRes.data.data
    
    // Pre-fill company and warehouse data
    if (companySettings.value) {
      form.customer_name = companySettings.value.company_name
      form.customer_phone = companySettings.value.phone
      form.customer_email = companySettings.value.email
      form.delivery_address = companySettings.value.warehouse_address
      form.delivery_city = companySettings.value.warehouse_city
      form.delivery_postal_code = companySettings.value.warehouse_postal_code
    }
  } catch (err) {
    error.value = 'Nie udało się załadować danych'
    console.error('Failed to load data:', err)
  } finally {
    loading.value = false
  }
})

const onProductChange = () => {
  // Product details are now displayed reactively via selectedProduct computed
}

const handleSubmit = async () => {
  error.value = null
  loading.value = true

  try {
    await productionStore.createOrder(form)
    
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

.loading-alert {
  padding: 1rem;
  background: var(--primary);
  color: var(--darker);
  border-radius: 8px;
  margin-bottom: 1.5rem;
  text-align: center;
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

.form-section h3 {
  color: rgba(255, 255, 255, 0.9);
  font-size: 1.1rem;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
}

.product-details {
  margin-top: 1.5rem;
  padding: 1rem;
  background: var(--dark);
  border: 1px solid rgba(0, 245, 255, 0.1);
  border-radius: 8px;
}

.product-details p {
  margin: 0.5rem 0;
  color: rgba(255, 255, 255, 0.8);
}

.product-details pre {
  background: var(--darker);
  padding: 0.75rem;
  border-radius: 6px;
  overflow-x: auto;
  font-size: 0.85rem;
  margin-top: 0.5rem;
}

.info-box {
  padding: 1rem;
  background: var(--dark);
  border: 1px solid rgba(0, 245, 255, 0.15);
  border-radius: 8px;
  margin-bottom: 1rem;
}

.info-box p {
  margin: 0.5rem 0;
  color: rgba(255, 255, 255, 0.9);
}

.help-text {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.5);
  font-style: italic;
  margin-top: 0.5rem;
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

.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
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

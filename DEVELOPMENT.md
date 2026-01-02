# Development Best Practices - Window Factory

This document outlines coding standards, best practices, and conventions for developing the Window Factory system.

## Code Style and Standards

### PHP (Backend)

#### PSR-12 Standard
Follow PSR-12 coding standard strictly. Use PHP-CS-Fixer to automate:

```bash
composer require --dev friendsofphp/php-cs-fixer
./vendor/bin/php-cs-fixer fix
```

#### Naming Conventions
- **Classes**: PascalCase (`UserController`, `OrderService`)
- **Methods**: camelCase (`getUserOrders`, `calculateTotal`)
- **Variables**: camelCase (`$userName`, `$orderItems`)
- **Constants**: UPPER_SNAKE_CASE (`MAX_RETRY_ATTEMPTS`)
- **Database tables**: snake_case (`production_orders`, `order_items`)

#### Example Controller
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->getAllOrders(
            filters: $request->only(['status', 'customer']),
            perPage: $request->get('per_page', 15)
        );

        return response()->json($orders);
    }
}
```

#### Service Layer Pattern
Always use services for business logic:

```php
namespace App\Services;

use App\Models\Order;
use App\Events\OrderCreated;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create($data['order']);
            
            foreach ($data['items'] as $item) {
                $order->items()->create($item);
            }
            
            event(new OrderCreated($order));
            
            return $order->load('items');
        });
    }
}
```

### JavaScript/Vue.js (Frontend)

#### ESLint Configuration
Use ESLint with Vue plugin:

```javascript
// .eslintrc.js
module.exports = {
  extends: [
    'plugin:vue/vue3-recommended',
    'eslint:recommended'
  ],
  rules: {
    'vue/multi-word-component-names': 'error',
    'vue/component-name-in-template-casing': ['error', 'PascalCase'],
    'no-console': process.env.NODE_ENV === 'production' ? 'error' : 'warn',
    'no-debugger': process.env.NODE_ENV === 'production' ? 'error' : 'warn'
  }
}
```

#### Composition API Best Practices

**✅ Good - Using Composition API:**
```vue
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useOrderStore } from '@/stores/orders'

const orderStore = useOrderStore()
const loading = ref(false)
const selectedOrder = ref(null)

const totalAmount = computed(() => {
  return selectedOrder.value?.items.reduce((sum, item) => sum + item.total, 0) ?? 0
})

const fetchOrder = async (id) => {
  loading.value = true
  try {
    selectedOrder.value = await orderStore.getOrder(id)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchOrder(1)
})
</script>

<template>
  <div class="order-details">
    <div v-if="loading">Loading...</div>
    <div v-else-if="selectedOrder">
      <h2>Order #{{ selectedOrder.id }}</h2>
      <p>Total: ${{ totalAmount }}</p>
    </div>
  </div>
</template>
```

**❌ Bad - Mixing Options API:**
```vue
<!-- Don't do this -->
<script>
export default {
  data() {
    return {
      order: null
    }
  },
  methods: {
    fetchOrder() {
      // Old style - avoid
    }
  }
}
</script>
```

#### Component Structure
```vue
<script setup>
// 1. Imports
import { ref } from 'vue'
import { useRouter } from 'vue-router'

// 2. Props & Emits
const props = defineProps({
  orderId: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['order-updated'])

// 3. Composables
const router = useRouter()

// 4. Reactive State
const loading = ref(false)
const error = ref(null)

// 5. Computed
const isValid = computed(() => {
  // validation logic
})

// 6. Methods
const handleSubmit = async () => {
  // logic
}

// 7. Lifecycle
onMounted(() => {
  // initialization
})
</script>

<template>
  <!-- Clean, semantic HTML -->
</template>

<style scoped>
/* Component-specific styles */
</style>
```

## Git Workflow

### Branch Naming
- `main` - Production-ready code
- `develop` - Development branch
- `feature/feature-name` - New features
- `bugfix/bug-description` - Bug fixes
- `hotfix/critical-fix` - Emergency production fixes
- `refactor/refactor-name` - Code refactoring

### Commit Messages
Follow Conventional Commits:

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting)
- `refactor`: Code refactoring
- `test`: Adding tests
- `chore`: Maintenance tasks

**Examples:**
```
feat(orders): add order cancellation functionality

Implement order cancellation with notification system.
Users can now cancel orders before production starts.

Closes #123
```

```
fix(auth): resolve token expiration issue

Token was expiring too quickly due to incorrect
timestamp calculation. Updated to use proper UTC time.

Fixes #456
```

## Testing Standards

### Backend Tests

#### Feature Test Example
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_order(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->postJson('/api/orders', [
                'customer_name' => 'John Doe',
                'items' => [
                    ['product_id' => 1, 'quantity' => 2]
                ]
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'customer_name',
                'items'
            ]);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'John Doe'
        ]);
    }
}
```

#### Unit Test Example
```php
<?php

namespace Tests\Unit;

use App\Services\PriceCalculator;
use Tests\TestCase;

class PriceCalculatorTest extends TestCase
{
    public function test_calculates_total_with_tax(): void
    {
        $calculator = new PriceCalculator();
        
        $total = $calculator->calculateWithTax(100, 0.23);
        
        $this->assertEquals(123, $total);
    }
}
```

### Frontend Tests

#### Component Test Example
```javascript
import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import OrderCard from '@/components/OrderCard.vue'

describe('OrderCard', () => {
  it('displays order information correctly', () => {
    const wrapper = mount(OrderCard, {
      props: {
        order: {
          id: 1,
          customer_name: 'John Doe',
          total: 1000
        }
      }
    })

    expect(wrapper.text()).toContain('John Doe')
    expect(wrapper.text()).toContain('1000')
  })

  it('emits delete event when delete button clicked', async () => {
    const wrapper = mount(OrderCard, {
      props: {
        order: { id: 1, customer_name: 'Test' }
      }
    })

    await wrapper.find('.delete-button').trigger('click')
    
    expect(wrapper.emitted('delete')).toBeTruthy()
  })
})
```

## API Design Principles

### RESTful Conventions
```
GET    /api/orders              - List all orders
GET    /api/orders/{id}         - Get single order
POST   /api/orders              - Create new order
PUT    /api/orders/{id}         - Update entire order
PATCH  /api/orders/{id}         - Partial update
DELETE /api/orders/{id}         - Delete order
```

### Request Validation
Always validate requests using Form Requests:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Order::class);
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:windows,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Order must have at least one item',
            'items.*.product_id.exists' => 'Invalid product selected',
        ];
    }
}
```

### Response Format
Consistent JSON response structure:

```json
// Success
{
  "data": {
    "id": 1,
    "name": "Order #1"
  }
}

// Error
{
  "message": "Validation failed",
  "errors": {
    "customer_name": ["The customer name field is required."]
  }
}
```

## Database Best Practices

### Migration Structure
```php
public function up(): void
{
    Schema::create('production_orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained()->onDelete('cascade');
        $table->foreignId('assigned_to')->nullable()->constrained('users');
        $table->enum('status', ['pending', 'in_progress', 'completed']);
        $table->decimal('total_cost', 10, 2);
        $table->timestamp('started_at')->nullable();
        $table->timestamp('completed_at')->nullable();
        $table->timestamps();
        $table->softDeletes();
        
        $table->index(['status', 'created_at']);
    });
}
```

### Query Optimization
```php
// ✅ Good - Eager loading
$orders = Order::with(['items', 'customer'])->get();

// ❌ Bad - N+1 problem
$orders = Order::all();
foreach ($orders as $order) {
    echo $order->customer->name; // Separate query for each!
}

// ✅ Good - Chunking large datasets
Order::chunk(100, function ($orders) {
    foreach ($orders as $order) {
        // Process order
    }
});
```

## Security Best Practices

### Input Sanitization
```php
// Always validate and sanitize
$validated = $request->validate([
    'comment' => 'required|string|max:1000'
]);

$comment = strip_tags($validated['comment']);
```

### SQL Injection Prevention
```php
// ✅ Good - Use Eloquent or Query Builder
User::where('email', $email)->first();

// ❌ Bad - Raw SQL with user input
DB::select("SELECT * FROM users WHERE email = '$email'");

// ✅ Good - If raw SQL necessary, use bindings
DB::select('SELECT * FROM users WHERE email = ?', [$email]);
```

### XSS Prevention
```vue
<!-- ✅ Good - Vue automatically escapes -->
<div>{{ userInput }}</div>

<!-- ❌ Bad - Raw HTML -->
<div v-html="userInput"></div>

<!-- ✅ Good - If HTML needed, sanitize first -->
<div v-html="sanitizedHtml"></div>
```

## Performance Optimization

### Backend Caching
```php
use Illuminate\Support\Facades\Cache;

// Cache expensive queries
$statistics = Cache::remember('dashboard.stats', 600, function () {
    return $this->dashboardService->getStatistics();
});

// Cache with tags
Cache::tags(['orders', 'statistics'])->remember('orders.stats', 600, fn() => 
    Order::selectRaw('status, count(*) as count')->groupBy('status')->get()
);

// Invalidate tagged cache
Cache::tags(['orders'])->flush();
```

### Frontend Performance
```javascript
// Lazy load routes
const routes = [
  {
    path: '/orders',
    component: () => import('./views/OrdersView.vue')
  }
]

// Debounce expensive operations
import { debounce } from 'lodash-es'

const search = debounce((query) => {
  // API call
}, 300)
```

## Code Review Checklist

### For Reviewers
- [ ] Code follows project standards
- [ ] Tests are included and passing
- [ ] No security vulnerabilities
- [ ] Performance implications considered
- [ ] Documentation updated
- [ ] Commits are clean and descriptive
- [ ] No debugging code left
- [ ] Error handling is appropriate

### For Developers
- [ ] Self-review completed
- [ ] All tests pass locally
- [ ] No console.log statements
- [ ] Code is formatted properly
- [ ] Comments explain "why", not "what"
- [ ] Branch is up to date with main

## Documentation Standards

### Inline Comments
```php
/**
 * Calculate the total price including taxes and discounts
 *
 * @param float $basePrice Base price before calculations
 * @param float $taxRate Tax rate as decimal (0.23 for 23%)
 * @param float|null $discount Optional discount amount
 * @return float Final calculated price
 * @throws InvalidArgumentException If price is negative
 */
public function calculateTotal(float $basePrice, float $taxRate, ?float $discount = null): float
{
    // Implementation
}
```

### Component Documentation
```vue
<script setup>
/**
 * OrderCard Component
 * 
 * Displays order information in a card format with actions.
 * Emits events for edit, delete, and view actions.
 * 
 * @component
 * @example
 * <OrderCard 
 *   :order="orderData"
 *   @edit="handleEdit"
 *   @delete="handleDelete"
 * />
 */

defineProps({
  /** The order object to display */
  order: {
    type: Object,
    required: true
  }
})
</script>
```

---

**Version:** 1.0.0  
**Last Updated:** January 2026

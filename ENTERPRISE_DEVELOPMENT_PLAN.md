# PIANO DI SVILUPPO ENTERPRISE - CARTINO

**Data:** 2025-12-24
**Versione:** 1.0
**Status:** In Corso

---

## 📊 ANALISI ENTERPRISE TEST CASES

### Tabella Supporto Funzionalità

| # | Funzionalità | Supporto | Note |
|---|--------------|----------|------|
| 1 | **Multi-site con cataloghi separati** | 🟡 PARZIALE | Modelli presenti (Site, Catalog), API `/catalogs?site=X` mancante |
| 2 | **Multi-market pricing & rules** | ✅ **SI** | PricingContext + PriceResolutionService completo |
| 3 | **Multi-currency catalog display** | 🟡 PARZIALE | Currency model OK, endpoint `/products?currency=X` non testato |
| 4 | **B2B company accounts** | ❌ **NO** | Customer B2B fields OK, mancano Company + multi-buyer |
| 5 | **B2B checkout approval flow** | ❌ **NO** | Nessun sistema OrderApproval o workflow approvazione |
| 6 | **B2C standard checkout** | ❌ **NO** | Solo `/checkout` singolo, mancano step multipli `/step/address` ecc |
| 7 | **Custom checkout step** | ❌ **NO** | Nessun sistema step injection (es. pickup location) |
| 8 | **Order status dynamic pipeline** | 🟡 PARZIALE | OrderState system OK, pochi handler automation |
| 9 | **Payment status dynamic pipeline** | ❌ **NO** | Nessun PaymentState separato né webhook handlers |
| 10 | **Product variants with attributes** | 🟡 PARZIALE | Struttura DB completa, API filter `/products?size=L&color=red` mancante |

**Legenda:**
- ✅ **SI** = Completamente supportato e testabile
- 🟡 **PARZIALE** = Infrastruttura presente, API/feature incomplete
- ❌ **NO** = Non implementato

---

## 🚀 PIANO DI SVILUPPO

### FASE 1: B2B Company System (Priorità ALTA)
**Effort:** 2-3 settimane
**Test Cases Risolti:** #4, #5

#### 1.1 Database & Models
```php
// File da creare:
- src/Models/Company.php
- src/Models/CompanyUser.php (pivot con role)
- src/Models/OrderApproval.php
- database/migrations/create_companies_table.php
- database/migrations/create_company_user_table.php
- database/migrations/create_order_approvals_table.php
```

**Company Features:**
- `company_number` (auto-generated COMP-000001)
- `name`, `vat_number`, `tax_id`
- `credit_limit`, `payment_terms` (NET30, NET60)
- `parent_company_id` (hierarchy support)
- `approval_threshold` (€1000 → richiede manager)
- `status` (active, suspended, closed)

**CompanyUser Features:**
- `company_id`, `user_id`
- `role` (buyer, manager, admin, finance)
- `can_approve_orders`, `approval_limit`
- `is_default_approver`

**OrderApproval Features:**
- `order_id`, `requested_by_id`, `approver_id`
- `status` (pending, approved, rejected)
- `threshold_exceeded`, `approval_reason`
- `approved_at`, `rejected_at`, `notes`

#### 1.2 Services
```php
// File da creare:
- src/Services/CompanyService.php
  - createCompany()
  - assignUsersToCompany()
  - setApprovalThresholds()

- src/Services/OrderApprovalService.php
  - requestApproval(Order $order)
  - approve(OrderApproval $approval, User $approver)
  - reject(OrderApproval $approval, string $reason)
  - notifyApprovers(Order $order)
```

#### 1.3 API Endpoints
```php
// routes/api.php
Route::prefix('companies')->group(function() {
    Route::get('/', [CompanyController::class, 'index']);
    Route::post('/', [CompanyController::class, 'store']);
    Route::get('/{company}', [CompanyController::class, 'show']);
    Route::patch('/{company}', [CompanyController::class, 'update']);

    Route::post('/{company}/users', [CompanyController::class, 'addUser']);
    Route::delete('/{company}/users/{user}', [CompanyController::class, 'removeUser']);
    Route::patch('/{company}/users/{user}/role', [CompanyController::class, 'updateUserRole']);
});

Route::prefix('orders')->group(function() {
    Route::post('/{order}/request-approval', [OrderApprovalController::class, 'request']);
    Route::post('/approvals/{approval}/approve', [OrderApprovalController::class, 'approve']);
    Route::post('/approvals/{approval}/reject', [OrderApprovalController::class, 'reject']);
    Route::get('/approvals/pending', [OrderApprovalController::class, 'pending']);
});
```

#### 1.4 Workflow Integration
```php
// src/Workflows/Actions/CheckApprovalRequired.php
- Se order.total > company.approval_threshold → crea OrderApproval
- Notifica manager via email/webhook
- Blocca order.status fino ad approvazione

// src/Workflows/Actions/ProcessApproval.php
- Approval approved → order.status = 'processing'
- Approval rejected → order.status = 'cancelled'
```

#### 1.5 Tests
```php
// tests/Feature/B2B/CompanyAccountTest.php
- test_create_company_with_multiple_buyers()
- test_buyer_can_place_order_below_threshold()
- test_order_above_threshold_requires_approval()
- test_manager_can_approve_order()
- test_manager_can_reject_order()
- test_approval_updates_order_status()
```

---

### FASE 2: Multi-Step Checkout (Priorità ALTA)
**Effort:** 1-2 settimane
**Test Cases Risolti:** #6, #7

#### 2.1 Checkout Step Registry
```php
// File da creare:
- src/Services/Checkout/CheckoutStepRegistry.php
- src/Services/Checkout/CheckoutFlowManager.php
- src/Services/Checkout/Steps/AddressStep.php
- src/Services/Checkout/Steps/ShippingStep.php
- src/Services/Checkout/Steps/PaymentStep.php
- src/Services/Checkout/Steps/ConfirmStep.php
- src/Contracts/CheckoutStepInterface.php
```

**CheckoutStepRegistry:**
```php
class CheckoutStepRegistry {
    protected array $steps = [
        'address' => AddressStep::class,
        'shipping' => ShippingStep::class,
        'payment' => PaymentStep::class,
        'confirm' => ConfirmStep::class,
    ];

    public function registerStep(string $name, string $class, int $position = null);
    public function getStep(string $name): CheckoutStepInterface;
    public function getFlow(): array;
}
```

**CheckoutStepInterface:**
```php
interface CheckoutStepInterface {
    public function validate(Request $request, Cart $cart): bool;
    public function process(Request $request, Cart $cart): array;
    public function getNextStep(): ?string;
    public function getPreviousStep(): ?string;
    public function canSkip(Cart $cart): bool;
}
```

#### 2.2 API Endpoints
```php
// routes/api.php
Route::prefix('checkout')->middleware('auth:sanctum')->group(function() {
    Route::get('/flow', [CheckoutController::class, 'getFlow']);
    Route::get('/step/{step}', [CheckoutController::class, 'getStep']);
    Route::post('/step/{step}', [CheckoutController::class, 'processStep']);
    Route::get('/current', [CheckoutController::class, 'getCurrentStep']);
});

// Esempi:
POST /checkout/step/address
{
  "billing_address": {...},
  "shipping_address": {...},
  "same_as_billing": true
}

POST /checkout/step/shipping
{
  "shipping_method_id": 2,
  "delivery_notes": "..."
}

POST /checkout/step/payment
{
  "payment_method_id": 1,
  "payment_details": {...}
}

POST /checkout/step/confirm
{
  "accept_terms": true
}
```

#### 2.3 Custom Step Example (Pickup Location)
```php
// src/Services/Checkout/Steps/PickupLocationStep.php
class PickupLocationStep implements CheckoutStepInterface {
    public function validate(Request $request, Cart $cart): bool {
        return $request->has('pickup_location_id') &&
               PickupLocation::find($request->pickup_location_id)->exists();
    }

    public function process(Request $request, Cart $cart): array {
        $cart->update([
            'pickup_location_id' => $request->pickup_location_id,
            'delivery_method' => 'pickup',
        ]);

        return ['next_step' => 'payment'];
    }
}

// Registrazione custom step:
// config/cartino.php
'checkout' => [
    'custom_steps' => [
        'pickup' => [
            'class' => PickupLocationStep::class,
            'position' => 2.5, // Tra shipping (2) e payment (3)
            'enabled' => true,
        ],
    ],
],
```

#### 2.4 Session Persistence
```php
// Migration: add checkout_session to carts table
$table->json('checkout_session')->nullable(); // {current_step, completed_steps[], data{}}

// CheckoutFlowManager
public function saveStepData(Cart $cart, string $step, array $data);
public function getStepData(Cart $cart, string $step): ?array;
public function markStepCompleted(Cart $cart, string $step);
public function canAccessStep(Cart $cart, string $step): bool;
```

#### 2.5 Tests
```php
// tests/Feature/Checkout/MultiStepCheckoutTest.php
- test_checkout_flow_returns_all_steps()
- test_process_address_step()
- test_process_shipping_step()
- test_process_payment_step()
- test_process_confirm_step_creates_order()
- test_custom_pickup_step_is_injected()
- test_cannot_skip_required_step()
- test_can_go_back_to_previous_step()
```

---

### FASE 3: API Filtering & Multi-Site (Priorità ALTA)
**Effort:** 1 settimana
**Test Cases Risolti:** #1, #3, #10

#### 3.1 Catalog Site Filtering
```php
// src/Http/Controllers/API/CatalogController.php
public function index(Request $request) {
    $query = Catalog::query();

    if ($request->has('site')) {
        $query->whereHas('sites', function($q) use ($request) {
            $q->where('sites.id', $request->site)
              ->orWhere('sites.slug', $request->site);
        });
    }

    return $query->with('products')->paginate();
}

// Endpoint:
GET /api/catalogs?site=siteA
GET /api/catalogs?site=1
```

#### 3.2 Product Variant Attribute Filtering
```php
// src/Http/Controllers/API/ProductController.php
public function index(Request $request) {
    $query = Product::query();

    // Filter by variant attributes
    if ($request->has('filter.option')) {
        foreach ($request->input('filter.option') as $optionName => $value) {
            $query->whereHas('variants.optionValues', function($q) use ($optionName, $value) {
                $q->whereHas('option', function($q2) use ($optionName) {
                    $q2->where('name', $optionName);
                })->where('value', $value);
            });
        }
    }

    // Filter by currency (price exists)
    if ($request->has('currency')) {
        $query->whereHas('variants.prices', function($q) use ($request) {
            $q->where('currency', $request->currency);
        });
    }

    return $query->with(['variants.prices', 'variants.optionValues'])->paginate();
}

// Endpoints:
GET /api/products?filter[option][Size]=L&filter[option][Color]=red
GET /api/products?currency=USD
GET /api/products?currency=EUR&filter[option][Size]=XL
```

#### 3.3 Tests
```php
// tests/Feature/API/CatalogFilteringTest.php
- test_catalogs_filtered_by_site()
- test_catalogs_show_only_site_products()
- test_site_a_catalog_isolated_from_site_b()

// tests/Feature/API/ProductVariantFilteringTest.php
- test_filter_products_by_single_attribute()
- test_filter_products_by_multiple_attributes()
- test_filter_products_by_currency()
- test_combined_filters()
```

---

### FASE 4: Workflow Automation & Handlers (Priorità MEDIA)
**Effort:** 2 settimane
**Test Cases Risolti:** #8, #9

#### 4.1 Order State Handlers
```php
// File da creare:
- src/Workflows/Actions/Order/SendConfirmationEmail.php
- src/Workflows/Actions/Order/UpdateInventoryOnPaid.php
- src/Workflows/Actions/Order/NotifyWarehouseOnProcessing.php
- src/Workflows/Actions/Order/GenerateShippingLabel.php
- src/Workflows/Actions/Order/UpdateTrackingInfo.php
- src/Workflows/Actions/Order/RefundPaymentOnCancelled.php
```

**Esempio: SendConfirmationEmail**
```php
class SendConfirmationEmail implements WorkflowActionInterface {
    public function execute(Order $order): void {
        Mail::to($order->customer->email)->send(
            new OrderConfirmationMail($order)
        );

        $order->update([
            'confirmation_sent_at' => now(),
        ]);
    }

    public function shouldExecute(Order $order): bool {
        return $order->state->slug === 'paid' && !$order->confirmation_sent_at;
    }
}
```

**Registrazione Actions:**
```php
// config/cartino.php
'workflows' => [
    'order_state_actions' => [
        'paid' => [
            SendConfirmationEmail::class,
            UpdateInventoryOnPaid::class,
        ],
        'processing' => [
            NotifyWarehouseOnProcessing::class,
        ],
        'shipped' => [
            GenerateShippingLabel::class,
            UpdateTrackingInfo::class,
        ],
        'cancelled' => [
            RefundPaymentOnCancelled::class,
        ],
    ],
],
```

#### 4.2 Payment Webhook System
```php
// File da creare:
- src/Models/PaymentState.php
- src/Services/PaymentWebhookHandler.php
- src/Workflows/Actions/Payment/ProcessSuccessfulPayment.php
- src/Workflows/Actions/Payment/HandleFailedPayment.php
- src/Workflows/Actions/Payment/ProcessRefund.php
- database/migrations/create_payment_states_table.php
```

**PaymentState Model:**
```php
- name (pending, processing, completed, failed, refunded)
- slug, description
- is_final (completed/failed/refunded = true)
- notify_customer, notify_merchant
- color (UI indicator)
- actions[] (JSONB - workflow actions to trigger)
```

**Webhook Handler:**
```php
class PaymentWebhookHandler {
    public function handle(string $gateway, array $payload): void {
        $payment = Payment::where('gateway_reference', $payload['id'])->first();

        match($payload['status']) {
            'completed' => $this->handleSuccess($payment),
            'failed' => $this->handleFailure($payment),
            'refunded' => $this->handleRefund($payment),
        };
    }

    protected function handleSuccess(Payment $payment): void {
        $payment->update(['state_id' => PaymentState::completed()->id]);

        // Trigger workflow
        app(WorkflowManager::class)->execute('payment.completed', $payment);

        // Update order
        $payment->order->update(['state_id' => OrderState::paid()->id]);
    }
}
```

**API Endpoints:**
```php
POST /webhooks/stripe
POST /webhooks/paypal
POST /webhooks/braintree
```

#### 4.3 Tests
```php
// tests/Feature/Workflows/OrderStatePipelineTest.php
- test_order_paid_triggers_confirmation_email()
- test_order_paid_updates_inventory()
- test_order_processing_notifies_warehouse()
- test_order_cancelled_triggers_refund()

// tests/Feature/Workflows/PaymentWebhookTest.php
- test_stripe_webhook_completed_updates_payment_state()
- test_payment_completed_triggers_workflow()
- test_payment_failed_notifies_customer()
- test_payment_refund_updates_order_state()
```

---

### FASE 5: Test Coverage Enterprise (Priorità MEDIA)
**Effort:** 3-4 settimane
**Test Cases Risolti:** Tutti (220 tests = 11 features × 20 instances)

#### 5.1 Test Structure
```php
// tests/Enterprise/
├── MultiSiteTest.php (20 instances)
├── MultiMarketPricingTest.php (20 instances)
├── MultiCurrencyTest.php (20 instances)
├── B2BCompanyTest.php (20 instances)
├── B2BApprovalFlowTest.php (20 instances)
├── B2CCheckoutTest.php (20 instances)
├── CustomCheckoutStepTest.php (20 instances)
├── OrderStatePipelineTest.php (20 instances)
├── PaymentStatePipelineTest.php (20 instances)
└── ProductVariantFilterTest.php (20 instances)
```

#### 5.2 Esempio: MultiSiteTest
```php
class MultiSiteTest extends TestCase {
    /** @test */
    public function multi_site_catalogs_isolated_instance_1() {
        $siteA = Site::factory()->create(['slug' => 'site-a']);
        $siteB = Site::factory()->create(['slug' => 'site-b']);

        $catalogA = Catalog::factory()->create();
        $catalogB = Catalog::factory()->create();

        $siteA->catalogs()->attach($catalogA);
        $siteB->catalogs()->attach($catalogB);

        $productA = Product::factory()->create();
        $productB = Product::factory()->create();

        $catalogA->products()->attach($productA);
        $catalogB->products()->attach($productB);

        // Test API isolation
        $response = $this->getJson('/api/catalogs?site=site-a');
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $catalogA->id]);
        $response->assertJsonMissing(['id' => $catalogB->id]);

        // Test product isolation
        $this->assertTrue($catalogA->products->contains($productA));
        $this->assertFalse($catalogA->products->contains($productB));
    }

    // Ripeti per instance 2-20 con dati diversi
}
```

#### 5.3 Data Factory Generators
```php
// database/factories/EnterpriseTestDataFactory.php
class EnterpriseTestDataFactory {
    public static function generateMultiSiteScenario(int $instance): array {
        return [
            'sites' => Site::factory()->count(2)->create([
                'slug' => "site-{$instance}-" . Str::random(4),
            ]),
            'catalogs' => Catalog::factory()->count(2)->create(),
            'products' => Product::factory()->count(10)->create(),
        ];
    }

    public static function generateB2BScenario(int $instance): array {
        $company = Company::factory()->create([
            'approval_threshold' => rand(500, 2000),
        ]);

        return [
            'company' => $company,
            'buyers' => User::factory()->count(3)->create(),
            'managers' => User::factory()->count(2)->create(),
            'products' => Product::factory()->count(5)->create(),
        ];
    }
}
```

#### 5.4 Parallel Test Execution
```bash
# phpunit.xml
<phpunit>
    <testsuites>
        <testsuite name="Enterprise">
            <directory>tests/Enterprise</directory>
        </testsuite>
    </testsuites>
    <php>
        <env name="PARATEST_PROCESSES" value="10"/>
    </php>
</phpunit>

# Run tests
vendor/bin/paratest --testsuite=Enterprise --processes=10
```

---

### FASE 6: B2B Quote System (Priorità BASSA)
**Effort:** 2 settimane
**Test Cases Risolti:** Enhancement B2B

#### 6.1 Models
```php
// File da creare:
- src/Models/Quote.php
- src/Models/QuoteLine.php
- database/migrations/create_quotes_table.php
- database/migrations/create_quote_lines_table.php
```

**Quote Features:**
```php
- quote_number (auto-generated QUOT-000001)
- company_id, customer_id
- status (draft, sent, accepted, rejected, expired, converted)
- subtotal, tax, shipping, total
- discount_percent, discount_amount
- expires_at, valid_until
- notes, terms_conditions
- converted_order_id (FK to orders)
- converted_at
- created_by_id (sales rep)
```

#### 6.2 Quote Negotiation Flow
```php
// src/Services/QuoteService.php
class QuoteService {
    public function create(Company $company, array $items): Quote;
    public function send(Quote $quote): void; // Email to customer
    public function accept(Quote $quote): Order; // Convert to order
    public function reject(Quote $quote, string $reason): void;
    public function revise(Quote $quote, array $changes): Quote; // Create new version
    public function extend(Quote $quote, Carbon $newExpiry): void;
}
```

#### 6.3 API Endpoints
```php
Route::prefix('quotes')->group(function() {
    Route::get('/', [QuoteController::class, 'index']);
    Route::post('/', [QuoteController::class, 'store']);
    Route::get('/{quote}', [QuoteController::class, 'show']);
    Route::patch('/{quote}', [QuoteController::class, 'update']);

    Route::post('/{quote}/send', [QuoteController::class, 'send']);
    Route::post('/{quote}/accept', [QuoteController::class, 'accept']);
    Route::post('/{quote}/reject', [QuoteController::class, 'reject']);
    Route::post('/{quote}/revise', [QuoteController::class, 'revise']);
});
```

---

## 📅 TIMELINE COMPLESSIVO

| Fase | Durata | Effort | Team Size |
|------|--------|--------|-----------|
| **FASE 1:** B2B Company System | 2-3 sett | 80-120h | 2 dev |
| **FASE 2:** Multi-Step Checkout | 1-2 sett | 40-80h | 1-2 dev |
| **FASE 3:** API Filtering | 1 sett | 40h | 1 dev |
| **FASE 4:** Workflow Automation | 2 sett | 80h | 2 dev |
| **FASE 5:** Test Coverage (220 tests) | 3-4 sett | 120-160h | 2-3 dev |
| **FASE 6:** B2B Quote System | 2 sett | 80h | 1-2 dev |
| **TOTALE** | **11-16 settimane** | **440-560h** | **2-3 dev** |

---

## 🎯 MILESTONE VERIFICABILI

### Milestone 1: B2B Ready (Fine Fase 1)
- [ ] 20× B2B company accounts tests passano
- [ ] 20× B2B approval flow tests passano
- [ ] API `/companies` completo e documentato
- [ ] Workflow approval integrato

### Milestone 2: Checkout Complete (Fine Fase 2)
- [ ] 20× B2C checkout tests passano
- [ ] 20× Custom checkout step tests passano
- [ ] API `/checkout/step/*` completo
- [ ] Esempio pickup location funzionante

### Milestone 3: API Complete (Fine Fase 3)
- [ ] 20× Multi-site isolation tests passano
- [ ] 20× Multi-currency tests passano
- [ ] 20× Variant filtering tests passano
- [ ] Tutti gli endpoint documentati in Postman/Swagger

### Milestone 4: Enterprise Ready (Fine Fase 5)
- [ ] **220 test cases passano** (11 features × 20 instances)
- [ ] Test coverage >80%
- [ ] Performance test: 10k products, 100 concurrent users
- [ ] Load test: 1000 orders/hour

---

## 🔧 TOOLS & AUTOMAZIONE

### CI/CD Pipeline
```yaml
# .github/workflows/enterprise-tests.yml
name: Enterprise Test Suite
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        test-suite:
          - MultiSite
          - B2B
          - Checkout
          - Workflow
          - ProductVariants
    steps:
      - uses: actions/checkout@v2
      - name: Run Test Suite
        run: vendor/bin/paratest --testsuite=${{ matrix.test-suite }} --processes=4
```

### Documentation Generator
```bash
# Generate API documentation from tests
php artisan api:generate-docs --from-tests=Enterprise
```

### Performance Monitoring
```php
// tests/Performance/EnterpriseLoadTest.php
- test_1000_concurrent_catalog_requests()
- test_100_simultaneous_checkouts()
- test_50_approval_workflows_parallel()
```

---

## 📝 NOTE IMPLEMENTAZIONE

### Pattern da Seguire
- Seguire stessa struttura dei controller esistenti
- Usare stesso naming convention (camelCase per metodi, snake_case per DB)
- Mantenere stessa architettura Service Layer
- Seguire pattern Eloquent relationships esistenti
- Usare stesso sistema di validation (FormRequest)

### Non Rompere
- Non modificare migrations esistenti (solo nuove)
- Non cambiare signature metodi pubblici esistenti
- Non rimuovere relazioni esistenti
- Mantenere backward compatibility API

### Testing
- Ogni nuova feature deve avere test
- Test must be isolated (no side effects)
- Use factories per test data
- Clean up after tests (database transactions)

---

**Report generato:** 2025-12-24
**Prossimo step:** FASE 3 - API Filtering (Quick Wins)

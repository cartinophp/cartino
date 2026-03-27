# Enum PHP Core - Technical Constants Only

## 🎯 Filosofia

**Solo costanti tecniche restano enum PHP. Tutto il business logic è nel DB.**

---

## ✅ Enum che RESTANO PHP (Solo 7)

Questi enum rappresentano **standard tecnici immutabili** e NON devono essere migrati nel DB.

### 1. Status (Generico)
**File:** [`src/Enums/Status.php`](../src/Enums/Status.php)

```php
enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Draft = 'draft';
    case Archived = 'archived';
    case Pending = 'pending';
    case Suspended = 'suspended';
    case Maintenance = 'maintenance';
    case Deprecated = 'deprecated';
    case Discontinued = 'discontinued';
    case Hidden = 'hidden';
    case Restricted = 'restricted';
}
```

**Motivo:** Stati tecnici generici usati per feature flags e stati di sistema. Non business-specific.

---

### 2. Gender
**File:** [`src/Enums/Gender.php`](../src/Enums/Gender.php)

```php
enum Gender: string
{
    case MALE = 'M';
    case FEMALE = 'F';
    case OTHER = 'other';
    case PREFER_NOT_TO_SAY = 'prefer_not_to_say';
}
```

**Motivo:** Standard internazionale. Valori fissi per compliance GDPR/privacy.

---

### 3. AddressType
**File:** [`src/Enums/AddressType.php`](../src/Enums/AddressType.php)

```php
enum AddressType: string
{
    case SHIPPING = 'shipping';
    case BILLING = 'billing';
}
```

**Motivo:** Standard tecnico. Shipping vs Billing è una distinzione universale.

---

### 4. MenuItemType
**File:** [`src/Enums/MenuItemType.php`](../src/Enums/MenuItemType.php)

```php
enum MenuItemType: string
{
    case LINK = 'link';
    case PAGE = 'page';
    case CUSTOM = 'custom';
}
```

**Motivo:** Tecnico. Definisce il tipo di comportamento del menu, non business logic.

---

### 5. CurrencyType
**File:** [`src/Enums/CurrencyLanguageEnums.php`](../src/Enums/CurrencyLanguageEnums.php)

```php
enum CurrencyType: string
{
    case FIAT = 'fiat';
    case CRYPTO = 'crypto';
    case COMMODITY = 'commodity';
    case DIGITAL = 'digital';
}
```

**Motivo:** Standard finanziario. Classificazione tecnica delle valute.

---

### 6. CurrencyStatus
**File:** [`src/Enums/CurrencyLanguageEnums.php`](../src/Enums/CurrencyLanguageEnums.php)

```php
enum CurrencyStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case DEPRECATED = 'deprecated';
}
```

**Motivo:** Stati tecnici per configurazione valute.

---

### 7. RegulatoryStatus
**File:** [`src/Enums/CurrencyLanguageEnums.php`](../src/Enums/CurrencyLanguageEnums.php)

```php
enum RegulatoryStatus: string
{
    case APPROVED = 'approved';
    case RESTRICTED = 'restricted';
    case BANNED = 'banned';
    case UNREGULATED = 'unregulated';
}
```

**Motivo:** Compliance e regolamentazione. Standard legale.

---

## ❌ Enum MIGRATI nel DB (27+)

Tutti questi sono ora **vocabularies DB**:

| Gruppo DB | Ex Enum PHP | Motivo Migrazione |
|-----------|-------------|-------------------|
| `order_status` | OrderStatus | Stati customizzabili per workflow cliente |
| `payment_status` | PaymentStatus | Gateway custom richiedono stati custom |
| `fulfillment_status` | FulfillmentStatus | Workflow logistica personalizzabile |
| `shipping_status` | - | (già nel seeder) |
| `return_status` | ReturnStatus | Policy reso diverse per cliente |
| `return_reason` | ReturnReason | Motivi custom per settore |
| `product_type` | ProductType | Tipologie prodotto custom (es: rental, lease) |
| `product_relation_type` | ProductRelationType | Cross-sell custom |
| `attribute_type` | AttributeType | Attributi custom per settore |
| `stock_status` | StockStatus | Stati scorte custom |
| `stock_movement_type` | StockMovementType | Movimenti magazzino custom |
| `stock_reservation_status` | StockReservationStatus | Prenotazioni custom |
| `stock_transfer_status` | StockTransferStatus | Trasferimenti custom |
| `inventory_location_type` | InventoryLocationType | Magazzini custom |
| `discount_type` | DiscountType | Sconti custom per settore |
| `discount_target_type` | DiscountTargetType | Regole sconto custom |
| `pricing_rule_type` | PricingRuleType | Pricing custom B2B/B2C |
| `shipping_method_type` | ShippingMethodType | Metodi spedizione custom |
| `shipping_calculation_method` | ShippingCalculationMethod | Calcoli custom |
| `supplier_status` | SupplierStatus | Stati fornitori custom |
| `purchase_order_status` | PurchaseOrderStatus | Workflow acquisti custom |
| `transaction_type` | TransactionType | Tipi pagamento custom |
| `transaction_status` | TransactionStatus | Stati transazione custom |
| `cart_status` | CartStatus | Stati carrello custom |
| `wishlist_status` | WishlistStatus | Stati wishlist custom |
| `app_status` | AppStatus | Stati marketplace custom |
| `app_installation_status` | AppInstallationStatus | Installazioni custom |

---

## 🔄 Come Usare gli Enum Core PHP

### Nel Codice

```php
use Cartino\Enums\Status;
use Cartino\Enums\Gender;
use Cartino\Enums\AddressType;

// ✅ Uso corretto - costanti tecniche
$user->status = Status::Active;
$customer->gender = Gender::FEMALE;
$address->type = AddressType::SHIPPING;
```

### Casting nei Model

```php
class User extends Model
{
    protected $casts = [
        'status' => Status::class, // ✅ Tecnico
        'gender' => Gender::class, // ✅ Standard
    ];
}
```

### Validazione

```php
use Illuminate\Validation\Rule;

$request->validate([
    'status' => ['required', Rule::enum(Status::class)],
    'gender' => ['required', Rule::enum(Gender::class)],
]);
```

---

## ❌ NON Usare Enum PHP Per

1. **Stati business** → Usa vocabularies DB
2. **Tipologie prodotto** → Usa vocabularies DB
3. **Metodi pagamento/spedizione** → Usa vocabularies DB
4. **Qualsiasi cosa che un cliente potrebbe voler customizzare** → Usa vocabularies DB

---

## 📏 Regola d'Oro

**Domanda:** "Questo valore potrebbe cambiare in base al cliente/tenant/settore?"

- **SÌ** → Vocabulary DB
- **NO** → Enum PHP

**Esempi:**

| Caso | Enum PHP? | Vocabulary DB? |
|------|-----------|----------------|
| Gender (M/F/Other) | ✅ Standard ISO | ❌ |
| Order Status | ❌ | ✅ Workflow custom |
| AddressType (shipping/billing) | ✅ Universale | ❌ |
| ProductType (physical/digital) | ❌ | ✅ Potrebbe servire "rental" |
| MenuItemType (link/page) | ✅ Tecnico | ❌ |
| Discount Type | ❌ | ✅ Settori hanno sconti diversi |

---

## 🛠️ Manutenzione

### Aggiungere un Nuovo Enum PHP Core

**Solo se** è una costante tecnica immutabile:

1. Crea enum in `src/Enums/`
2. Usa naming standard (PascalCase per casi, UPPERCASE per backed values se necessario)
3. Aggiungi docblock chiaro
4. Documenta il motivo in questo file

### Convertire Enum → Vocabulary

Se ti accorgi che un enum PHP core dovrebbe essere vocabulary:

1. Aggiungi gruppo in `VocabularySeeder`
2. Aggiungi al `VocabularyService`
3. Esegui seeder
4. Aggiorna model per usare relazione
5. Elimina enum PHP
6. Aggiorna documentazione

---

## 📊 Statistiche Finali

- **Enum PHP Core:** 7 (18%)
- **Vocabularies DB:** 27+ (82%)
- **Ratio:** ~1:4 (per ogni enum PHP, ci sono 4 vocabularies)

Questo dimostra che **la maggior parte dei "valori enum" in un e-commerce sono business logic, non costanti tecniche.**

---

## ✅ Checklist Nuovi Enum

Prima di creare un nuovo enum PHP, verifica:

- [ ] È uno standard internazionale? (es: ISO gender codes)
- [ ] È una distinzione tecnica universale? (es: shipping vs billing)
- [ ] Non cambierà mai per nessun cliente/settore?
- [ ] Non ha bisogno di traduzioni customizzate?
- [ ] Non richiede metadata variabile?

**Se anche solo UNA risposta è "NO" → Usa Vocabulary DB**

---

**Ultimo aggiornamento:** 2025-12-23
**Migrazione completata:** 27 enum → DB vocabularies

# Enum PHP vs Vocabularies DB - Decision Matrix

## 📊 Classificazione degli Enum di Cartino

### 🗄️ SPOSTARE NEL DB (Business Logic)

Questi **DEVONO** essere vocabolari perché sono customizzabili:

| Enum Attuale | Gruppo Vocabulary | Motivo |
|--------------|-------------------|--------|
| ✅ OrderStatus | `order_status` | Già fatto - stati custom per cliente |
| ✅ PaymentStatus | `payment_status` | Già fatto - gateway custom |
| ✅ FulfillmentStatus | `fulfillment_status` | Già fatto |
| ✅ ShippingStatus | - | Già fatto come shipping_status |
| ✅ ReturnStatus | `return_status` | Già fatto |
| 🔄 ProductType | `product_type` | Già nel seeder - rimuovere enum |
| 🔄 StockStatus | `stock_status` | Già nel seeder - rimuovere enum |
| 🔄 DiscountType | `discount_type` | Sconti custom per settore |
| 🔄 DiscountTargetType | `discount_target_type` | Regole custom |
| 🔄 StockMovementType | `stock_movement_type` | Movimenti custom |
| 🔄 ShippingMethodType | `shipping_method_type` | Corrieri custom |
| 🔄 ShippingCalculationMethod | `shipping_calculation` | Logiche custom |
| 🔄 SupplierStatus | `supplier_status` | Stati custom fornitori |
| 🔄 PurchaseOrderStatus | `purchase_order_status` | Workflow custom |
| 🔄 TransactionType | `transaction_type` | Tipi pagamento custom |
| 🔄 TransactionStatus | `transaction_status` | Stati transazione |
| 🔄 CartStatus | `cart_status` | Stati carrello custom |
| 🔄 WishlistStatus | `wishlist_status` | Stati wishlist |
| 🔄 StockReservationStatus | `stock_reservation_status` | Prenotazioni |
| 🔄 StockTransferStatus | `stock_transfer_status` | Trasferimenti |
| 🔄 ReturnReason | `return_reason` | Motivi custom |
| 🔄 PricingRuleType | `pricing_rule_type` | Regole pricing |
| 🔄 ProductRelationType | `product_relation_type` | Cross-sell custom |
| 🔄 AttributeType | `attribute_type` | Attributi custom |
| 🔄 InventoryLocationType | `inventory_location_type` | Magazzini custom |
| 🔄 AppStatus | `app_status` | Stati app marketplace |
| 🔄 AppInstallationStatus | `app_installation_status` | Installazioni |

**Totale: ~27 enum → DB**

---

### 🔒 TENERE COME ENUM PHP (Sistema)

Questi restano enum perché sono **standard tecnici**:

| Enum Attuale | Motivo |
|--------------|--------|
| ❌ Status (generico) | Active/Inactive è tecnico, non business |
| ❌ Gender | Standard ISO (M/F/Other/Prefer not to say) |
| ❌ AddressType | Standard (shipping/billing) |
| ❌ MenuItemType | Tecnico (link/page/custom) |
| ❌ CurrencyType | Standard (FIAT/CRYPTO) |
| ❌ CurrencyStatus | Tecnico |
| ❌ RegulatoryStatus | Compliance standard |

**Totale: ~7 enum → Restano PHP**

---

## 🚀 Piano di Migrazione

### Step 1: Aggiungi Seeder per Nuovi Vocabolari

Espandi `VocabularySeeder` con tutti i gruppi mancanti.

### Step 2: Migra Model che Usano Enum

Esempio per `Product`:

**PRIMA (enum)**:
```php
use Cartino\Enums\ProductType;

protected $casts = [
    'type' => ProductType::class,
];
```

**DOPO (vocabulary)**:
```php
public function typeVocabulary(): BelongsTo
{
    return $this->belongsTo(Vocabulary::class, 'type', 'code')
        ->where('group', 'product_type');
}

public function getTypeLabelAttribute(): string
{
    return $this->typeVocabulary?->getLabel() ?? $this->type;
}
```

### Step 3: Rimuovi Enum File

Elimina gli enum che sono ora vocabolari.

### Step 4: Aggiorna Frontend

Nessun cambiamento! Il middleware Inertia condivide già tutti i vocabolari.

---

## 🎨 Regola d'Oro

**Se ti fai questa domanda:**

> "Un cliente potrebbe voler aggiungere/modificare/tradurre diversamente questo valore?"

- **SÌ** → Vocabulary DB
- **NO** → Enum PHP

---

## 📝 Esempi Pratici

### Caso 1: Stati Ordine
❓ "Un cliente vuole aggiungere stato 'In Quality Check'?"
✅ **SÌ** → Vocabulary DB

### Caso 2: Gender
❓ "Un cliente vuole cambiare M/F/Other?"
❌ **NO** → Enum PHP (standard)

### Caso 3: Discount Type
❓ "Un cliente B2B vuole 'Volume Discount' custom?"
✅ **SÌ** → Vocabulary DB

### Caso 4: Address Type
❓ "Un cliente vuole aggiungere 'gift_recipient'?"
🤔 **FORSE** → Dipende se è ricorrente

---

## ⚠️ Warning: Quando NON usare DB

NON usare vocabularies per:

1. **Valori con logica hardcoded**
   ```php
   // BAD - troppa logica dipendente dall'enum
   if ($product->type === ProductType::Digital) {
       // 50 righe di logica specifica
   }
   ```

2. **Valori validati da API esterne**
   ```php
   // Standard ISO gender codes
   enum Gender: string {
       case MALE = 'M';
       case FEMALE = 'F';
   }
   ```

3. **Feature flags**
   ```php
   enum Feature: string {
       case ADVANCED_ANALYTICS = 'analytics';
   }
   ```

---

## 📊 Recap Finale

| Categoria | DB Vocabulary | Enum PHP |
|-----------|---------------|----------|
| **Quantità** | ~27 (73%) | ~7 (27%) |
| **Esempi** | Order status, Product types | Gender, Address type |
| **Modificabile** | ✅ Da admin/plugin | ❌ Solo via deploy |
| **Traduzioni** | ✅ Customizzabili | ⚠️ File lang fissi |
| **Frontend** | ✅ Auto via Inertia | 🔧 Mapping manuale |

---

**Best Practice: La maggior parte degli "enum di business" devono essere vocabularies DB.**

Solo le costanti tecniche rimangono enum PHP.

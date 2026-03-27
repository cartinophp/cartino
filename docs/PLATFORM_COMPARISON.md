# Cartino vs E-commerce Platforms - Complete Comparison

Analisi comparativa approfondita tra Cartino e le principali piattaforme e-commerce enterprise.

## Piattaforme Analizzate

1. **PrestaShop** - PHP/MySQL, Open Source, 300k+ merchants
2. **Shopware** - PHP/Symfony, Enterprise-focused, German market leader
3. **Craft Commerce** - PHP/Yii/Craft CMS, Premium, content-first
4. **Shopify** - Ruby/Rails, SaaS, 2M+ merchants, API-first
5. **Sylius** - PHP/Symfony, Headless-first, B2B-focused

---

## 📊 Quick Comparison Matrix

| Feature | Cartino | PrestaShop | Shopware | Craft Commerce | Shopify | Sylius |
|---------|---------|------------|----------|----------------|---------|--------|
| **Architecture** | Laravel | Custom MVC | Symfony | Yii2/Craft | Rails API | Symfony |
| **Headless** | ✅ Full | ❌ Limited | ✅ Full | ⚠️ Partial | ✅ Full | ✅ Full |
| **Multi-store** | ✅ | ✅ | ✅ | ⚠️ Plugin | ✅ | ✅ |
| **Multi-currency** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Variants** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Price Lists** | ✅ | ❌ | ✅ | ❌ | ❌ | ✅ |
| **Inventory** | ✅ | ✅ Basic | ✅ Advanced | ✅ Basic | ✅ Basic | ✅ Advanced |
| **B2B Features** | ⚠️ Partial | ⚠️ Plugin | ✅ Full | ❌ | ⚠️ Plus | ✅ Full |
| **Custom Fields** | ✅ | ✅ | ✅ | ✅ Best | ⚠️ Metafields | ✅ |
| **Asset Management** | ✅ Glide | ❌ Basic | ⚠️ DAM | ✅ Native | ⚠️ CDN | ⚠️ Basic |
| **API Quality** | ✅ RESTful | ⚠️ Mixed | ✅ RESTful | ✅ GraphQL | ✅ GraphQL | ✅ RESTful |
| **Performance** | ⚡ Excellent | ⚠️ Medium | ⚡ Excellent | ⚡ Good | ⚡ Excellent | ⚡ Good |
| **Scalability** | 🚀 5M+ | ⚠️ 100k | 🚀 1M+ | ⚠️ 50k | 🚀 Unlimited | 🚀 1M+ |

---

## 1. PRODUCT MANAGEMENT

### Cartino (Current)
```php
Product
├── id, name, slug, sku
├── description, short_description
├── price (base)
├── brand_id
├── product_type_id
├── status (draft/published/archived)
├── track_inventory
├── stock_quantity
├── weight, dimensions
├── meta (JSONB) - SEO
├── data (JSONB) - Custom fields
└── Variants (ProductVariant)
    ├── sku, barcode
    ├── price override
    ├── stock_quantity
    ├── options (size, color, etc.)
```

**✅ Strengths:**
- JSONB custom fields (`data`)
- Multi-variant support
- Clean structure

**❌ Missing:**
- Product bundles/kits
- Composite products
- Digital products (downloads)
- Rental/subscription products
- Product relationships (upsells, cross-sells, related)
- Product availability by channel/site
- Product publishing schedule
- Product indexing/search optimization

---

### PrestaShop
```sql
ps_product
├── id_product
├── id_supplier, id_manufacturer
├── id_category_default
├── reference (SKU)
├── price (tax excluded)
├── wholesale_price
├── unity (unit type)
├── unit_price_ratio
├── ecotax
├── quantity (deprecated, uses Stock)
├── minimal_quantity
├── available_for_order
├── condition (new/used/refurbished)
├── show_price
├── indexed (search)
├── visibility (everywhere/catalog/search/none)
└── Advanced features:
    ├── ps_product_attribute (Combinations = Variants)
    ├── ps_stock_available (Multi-warehouse)
    ├── ps_specific_price (Rules engine)
    ├── ps_pack (Product bundles)
    ├── ps_product_supplier (Multi-supplier)
    ├── ps_product_download (Virtual products)
```

**Key Features:**
- **Combinations** (variants) con stock separato
- **Packs** (bundles)
- **Virtual products** con download
- **Specific prices** (customer groups, quantity, dates)
- **Multi-supplier** management
- **Warehouse** multi-location
- **Condition** (new/used/refurbished)
- **Visibility** rules per catalog/search

**What Cartino is Missing:**
- ❌ Product bundles/packs
- ❌ Digital downloads
- ❌ Multi-warehouse stock
- ❌ Specific price rules (by customer group, quantity)
- ❌ Product condition field
- ❌ Visibility rules (catalog/search)

---

### Shopware
```php
product
├── id, productNumber (SKU), ean
├── manufacturerId, taxId
├── price (object with rules)
├── stock, availableStock
├── purchaseUnit, referenceUnit, packUnit
├── weight, width, height, length
├── releaseDate, createdAt
├── markAsTopseller
├── coverId (main image)
├── properties (custom fields)
├── Advanced:
    ├── variants (configurator)
    ├── prices (advanced price matrix)
    ├── visibilities (sales channels)
    ├── categories (many-to-many)
    ├── crossSellings
    ├── deliveryTime
    ├── purchasePrices (multiple)
```

**Key Features:**
- **Advanced Price Matrix** (scales, rules, contexts)
- **Sales Channel Visibility** (per-channel availability)
- **Configurator** (variant generator)
- **Cross-sellings** (upsell/related)
- **Delivery Time** tracking
- **Purchase prices** tracking
- **Stock management** con available vs reserved
- **Properties** (filterable attributes)

**What Cartino is Missing:**
- ❌ Price matrix/rules engine
- ❌ Sales channel visibility
- ❌ Advanced variant configurator
- ❌ Product cross-sellings
- ❌ Delivery time estimates
- ❌ Purchase price tracking
- ❌ Reserved stock vs available

---

### Craft Commerce
```php
Product (Craft Element)
├── Element fields (title, slug, etc.)
├── typeId (Product Type)
├── taxCategoryId
├── shippingCategoryId
├── promotable
├── freeShipping
├── enabled, expiryDate
├── defaultVariantId
└── Variants
    ├── sku, price
    ├── stock, hasUnlimitedStock
    ├── minQty, maxQty
    ├── length, width, height, weight
    ├── Custom fields (per variant!)
```

**Key Features:**
- **Element-based** (same as Entries, Assets)
- **Custom Fields** illimitati (Field Layouts)
- **Per-variant custom fields**
- **Product Types** con field layouts diversi
- **Matrix fields** (repeater data)
- **Promotable** flag
- **Expiry dates**
- **Min/Max quantity**
- **Unlimited stock** option

**What Cartino is Missing:**
- ❌ Per-variant custom fields (now only product-level)
- ❌ Min/Max quantity constraints
- ❌ Unlimited stock option
- ❌ Product expiry dates
- ❌ Promotable flag (for discount exclusions)
- ❌ Free shipping flag per product

---

### Shopify
```graphql
Product {
  id, handle
  title, description, descriptionHtml
  vendor (= brand)
  productType
  tags
  status (ACTIVE, ARCHIVED, DRAFT)
  publishedAt
  onlineStoreUrl
  priceRangeV2
  compareAtPriceRange
  featuredImage, media
  metafields (custom data)

  variants {
    sku, barcode
    price, compareAtPrice
    inventoryQuantity
    inventoryPolicy (DENY, CONTINUE)
    weight, weightUnit
    selectedOptions (size, color)
    availableForSale
  }

  collections (categories)
  publishedOnCurrentPublication
  requiresSellingPlan (subscriptions)
}
```

**Key Features:**
- **Metafields** (namespaced custom fields)
- **Media** (images, videos, 3D models)
- **Tags** (flexible taxonomy)
- **Inventory Policy** (allow backorder or not)
- **Selling Plans** (subscriptions)
- **Publications** (multi-storefront)
- **Handle** (permanent URL identifier)
- **Compare at price** (was/now pricing)

**What Cartino is Missing:**
- ❌ Inventory policy (allow backorder)
- ❌ Selling plans (subscriptions built-in)
- ❌ Publications/channels management
- ❌ Compare at price (list price vs sale)
- ❌ Permanent handle (slug can change)
- ❌ Rich media (videos, 3D models)

---

### Sylius
```php
Product
├── code (unique identifier)
├── enabled, createdAt, updatedAt
├── channels (multi-channel)
├── translations (i18n)
└── ProductVariant
    ├── code, position
    ├── tracked (inventory tracking)
    ├── onHand, onHold
    ├── channelPricings (per channel!)
    ├── weight, width, height, depth
    ├── shippingRequired
    ├── translations
```

**Key Features:**
- **Channel Pricings** (prezzi diversi per canale)
- **On Hand vs On Hold** stock
- **Translations** native
- **Tracked** flag (enable/disable tracking)
- **Shipping Required** flag
- **Taxons** (flexible taxonomy)
- **Associations** (upsell, related, accessories)
- **Product Options** (configurator)

**What Cartino is Missing:**
- ❌ Channel-specific pricing
- ❌ On Hold stock (reserved)
- ❌ Shipping Required flag
- ❌ Product Associations (upsell, cross-sell)
- ❌ Native i18n translations

---

## 2. PRICING STRATEGY

### Cartino (Current)
```php
Price
├── product_variant_id
├── price_list_id
├── price
├── currency
├── min_quantity (tiered pricing)
├── max_quantity
├── starts_at, ends_at
```

**✅ Strengths:**
- Price lists (B2B)
- Tiered pricing (quantity-based)
- Time-based pricing
- Multi-currency

**❌ Missing:**
- Customer group pricing
- Channel-specific pricing
- Zone-based pricing
- Tax-inclusive/exclusive toggle
- Cost price tracking
- Margin calculation

---

### PrestaShop - Specific Prices
```sql
ps_specific_price
├── id_product, id_product_attribute
├── id_shop, id_shop_group
├── id_currency
├── id_country, id_group (customer group)
├── id_customer (individual)
├── price (override)
├── from_quantity (tiered)
├── reduction (discount %)
├── reduction_type (percent/amount)
├── reduction_tax (included/excluded)
├── from, to (date range)
└── priority (rule resolution)
```

**Advanced Features:**
- **Priority system** (multiple rules)
- **Individual customer** pricing
- **Tax handling** in rules
- **Shop-specific** pricing
- **Country-specific** pricing

**What to Add to Cartino:**
```php
// Proposed: price_rules table
├── entity_type (product/variant/category)
├── entity_id
├── customer_id (optional)
├── customer_group_id (optional)
├── site_id, channel_id
├── country_id, zone_id
├── min_quantity
├── reduction_type (percent/amount)
├── reduction_value
├── priority
├── conditions (JSONB)
```

---

### Shopware - Advanced Price Matrix
```php
product_price
├── productId, productVersionId
├── ruleId (complex rule matching)
├── quantityStart, quantityEnd
├── currencyId
├── price (JSON with net/gross)
└── Rule Engine:
    ├── Customer groups
    ├── Sales channels
    ├── Currencies
    ├── Countries
    ├── Custom conditions
```

**What to Learn:**
- **Rule Engine** (condizioni complesse)
- **Version control** per prezzi
- **Net/Gross** sempre insieme
- **Inheritance** da parent a variants

**Cartino Should Add:**
```php
PriceRule (Rule Engine)
├── name, priority
├── conditions (JSONB)
│   ├── customer_group_ids
│   ├── country_ids
│   ├── channel_ids
│   ├── min_cart_value
│   ├── product_categories
│   └── custom_conditions
├── actions (JSONB)
│   ├── discount_type
│   ├── discount_value
│   └── override_price
```

---

### Craft Commerce - Pricing
```php
Variant
├── price (base)
├── Sale (global sales)
│   ├── apply (all/categories/products)
│   ├── percent/amount
│   ├── dates
└── No complex pricing
```

**Limitations:**
- ❌ No customer group pricing
- ❌ No tiered pricing
- ❌ No price lists
- Simple = good for content-first

**Cartino is Better Here!**

---

### Shopify - Pricing
```graphql
ProductVariant {
  price
  compareAtPrice (was/now)

  # Shopify Plus only:
  priceV2(presentmentCurrencyCode)
  compareAtPriceV2
}

PriceRule {
  id, title
  customerSelection (all/segment)
  target (line_item/shipping)
  value (percentage/fixed)
  prerequisiteQuantityRange
  prerequisiteSubtotalRange
  usageLimit
  startsAt, endsAt
}
```

**What Cartino is Missing:**
- ❌ Compare at price (strikethrough)
- ❌ Price rules (flexible discount engine)
- ❌ Prerequisite conditions
- ❌ Usage limits

---

### Sylius - Channel Pricing
```php
ChannelPricing
├── productVariantId
├── channelId
├── price (in channel currency)
├── originalPrice (compare at)
```

**Key Concept:**
- Ogni variante ha un **prezzo PER CANALE**
- No price lists (pricing is channel-specific)

**Cartino Could Add:**
```php
// Option 1: Extend Price model
Price::where('channel_id', $channel)->first()

// Option 2: Embed in variant
product_variants
├── prices (JSONB)
    ├── channel_1: {price: 100, currency: 'EUR'}
    ├── channel_2: {price: 120, currency: 'USD'}
```

---

## 3. INVENTORY MANAGEMENT

### Cartino (Current)
```php
ProductVariant
├── track_inventory (boolean)
├── stock_quantity (integer)
├── allow_backorder (boolean)
```

**✅ Simple**
**❌ Missing:**
- Multi-warehouse
- Reserved stock
- Stock movements history
- Low stock alerts
- Stock policies (continue selling, deny)

---

### PrestaShop - Stock Management
```sql
ps_stock_available
├── id_product, id_product_attribute
├── id_shop, id_shop_group
├── quantity (physical stock)
├── depends_on_stock (warehouse mode)
├── out_of_stock (deny/allow/default)

ps_stock (Warehouse mode)
├── id_warehouse
├── id_product, id_product_attribute
├── physical_quantity
├── usable_quantity
├── price_te (purchase price)

ps_stock_mvt (Movements)
├── id_employee
├── id_stock
├── physical_quantity (delta)
├── sign (increase/decrease)
├── reason (sale/return/stock_movement)
```

**Features:**
- **Multi-warehouse** stock tracking
- **Usable vs Physical** (reserved)
- **Stock movements** history
- **Out of stock** policies per product
- **Purchase price** tracking

**What Cartino Needs:**
```php
// Stock locations
StockLocation
├── name, type (warehouse/store/dropship)
├── address_id
├── priority

// Stock per location
Stock
├── product_variant_id
├── stock_location_id
├── quantity_on_hand
├── quantity_reserved
├── quantity_available (computed)

// Stock movements
StockMovement
├── stock_id
├── quantity (delta)
├── type (sale/purchase/adjustment/transfer)
├── order_id, purchase_order_id
├── user_id
├── notes
```

---

### Shopware - Stock Management
```php
product
├── stock (total available)
├── availableStock (total - reserved)
├── restock_time (days)
├── is_closeout (stop selling when out)
├── min_purchase (minimum order qty)
├── purchase_steps (increment)
```

**Features:**
- **Available Stock** calculation
- **Restock Time** estimates
- **Is Closeout** (auto-disable)
- **Min Purchase** (MOQ)
- **Purchase Steps** (sell in multiples)

**Cartino Should Add:**
```php
product_variants
├── quantity_on_hand
├── quantity_reserved (from active carts/orders)
├── quantity_available (computed)
├── restock_days
├── min_order_quantity
├── order_increment (sell in multiples of X)
├── is_closeout
```

---

### Shopify - Inventory Management
```graphql
InventoryLevel {
  available (sellable)
  incoming (on order)
  committed (reserved)
  damaged

  location {
    id, name
    address
    isActive, isPrimary
  }
}

InventoryItem {
  sku
  tracked
  inventoryPolicy (DENY, CONTINUE)
  countryCodeOfOrigin
  harmonizedSystemCode (customs)
  cost (purchase price)
}
```

**Features:**
- **Multi-location** native
- **Incoming stock** tracking
- **Damaged stock** tracking
- **Customs codes**
- **Cost tracking**

**Cartino Needs:**
```php
Warehouse
├── name, code
├── is_active, is_primary
├── address_id

InventoryLevel
├── product_variant_id
├── warehouse_id
├── quantity_on_hand
├── quantity_incoming (from POs)
├── quantity_committed (orders)
├── quantity_damaged
├── quantity_available (computed)

product_variants
├── inventory_policy (deny/continue)
├── country_of_origin
├── hs_code (customs)
├── cost_price
```

---

### Sylius - Inventory
```php
ProductVariant
├── tracked (boolean)
├── onHand (stock quantity)
├── onHold (reserved)
├── version (optimistic locking)

InventoryUnit (Order level tracking)
├── stockableId (variant)
├── state (sold/returned)
```

**Features:**
- **On Hold** (reserved stock)
- **Optimistic Locking** (race condition)
- **Inventory Units** (track cada item)

**Cartino Should Add:**
```php
OrderLine
├── inventory_units (JSON)
    [
      {warehouse_id: 1, quantity: 2},
      {warehouse_id: 2, quantity: 3}
    ]

// Reservations table
StockReservation
├── product_variant_id
├── warehouse_id
├── quantity
├── reserved_by (cart/order)
├── expires_at
```

---

## 4. CATEGORIES & TAXONOMIES

### Cartino (Current)
```php
Category
├── parent_id (nested)
├── title, slug
├── collection_type (manual/smart)
├── rules (smart collection query)
├── disjunctive (AND/OR rules)
```

**✅ Strengths:**
- Smart collections (Shopify-style)
- Nested categories

**❌ Missing:**
- Multiple taxonomies (tags, collections, types)
- Category visibility per channel
- Category custom fields
- Category images/assets
- Category SEO per language

---

### PrestaShop - Categories
```sql
ps_category
├── id_parent (nested set)
├── level_depth, nleft, nright (MPTT)
├── active, is_root_category
├── position

ps_category_lang
├── id_category, id_lang
├── name, description, link_rewrite
├── meta_title, meta_description

ps_category_product
├── id_category, id_product
├── position (manual sort)

ps_category_shop (Multi-store)
├── id_category, id_shop
```

**Features:**
- **MPTT** (Modified Preorder Tree Traversal) - Fast queries
- **Multi-language** nativo
- **Multi-store** availability
- **Manual product sorting**

**What Cartino Needs:**
```php
// Add MPTT for performance
categories
├── lft, rgt, depth (Nested Set)

// Multi-language
category_translations
├── category_id
├── locale
├── name, description, meta_title, meta_description

// Channel availability
category_channel
├── category_id
├── channel_id
├── is_visible
```

---

### Shopware - Categories
```php
category
├── parentId, level, path
├── type (page/link/folder)
├── visible, active
├── displayNestedProducts
├── productAssignmentType (product/category)
├── cmsPageId (landing page)
├── media (images)
└── Streams (dynamic products)
```

**Features:**
- **Category Types** (page/link/folder)
- **Display Nested** (show subcategory products)
- **CMS Pages** (category landing pages)
- **Streams** (dynamic product selection)

**Cartino Should Add:**
```php
categories
├── type (category/link/folder/page)
├── display_nested_products
├── cms_page_id (for landing pages)
├── stream_conditions (JSONB - for dynamic products)
```

---

### Craft Commerce - Product Types
```php
ProductType
├── name, handle
├── hasDimensions, hasVariants
├── titleFormat
├── fieldLayoutId (Custom Fields!)
└── Field Layout defines:
    ├── Text fields
    ├── Matrix fields (repeaters)
    ├── Assets fields
    ├── Relations
    └── Any Craft field type
```

**Key Concept:**
- **Product Types** determinano campi disponibili
- **Field Layouts** completamente customizable
- **Matrix fields** per dati complessi

**Cartino Has:**
- `ProductType` ✅
- `data` JSONB ✅
- But no visual field builder ❌

---

### Shopify - Collections & Tags
```graphql
Collection {
  id, handle, title
  ruleSet {
    appliedDisjunctively (AND/OR)
    rules {
      column (tag/title/vendor/variant_price)
      relation (equals/greater_than/contains)
      condition
    }
  }
  products (manual)
}

Product {
  tags (unlimited)
  productType
  vendor
  collections
}
```

**Features:**
- **Smart Collections** (rule-based)
- **Manual Collections**
- **Tags** (folksonomy)
- **Product Type** (taxonomy)
- **Vendor** (brand)

**Cartino Has This!** ✅ (collection_type, rules)

---

### Sylius - Taxons
```php
Taxon
├── code (unique)
├── parentId (tree)
├── position
├── translations
└── Flexible taxonomy:
    ├── Main taxonomy (categories)
    ├── Brand taxonomy
    ├── Tag taxonomy
    └── Custom taxonomies
```

**Key Concept:**
- **Multiple Taxonomies** (not just categories)
- Each taxonomy is a tree
- Products can be in multiple taxons

**Cartino Should Consider:**
```php
// Current: only categories
// Could add:
Taxonomy
├── id, name, code
├── type (categories/brands/tags/custom)

TaxonRelation
├── taxonomy_id
├── product_id
├── position
```

---

## 5. ORDERS & CHECKOUT

### Cartino (Current)
```php
Order
├── customer_id
├── number, status
├── subtotal, tax, shipping, total
├── billing_address, shipping_address
├── payment_method, payment_status
├── shipping_method
├── notes, data (JSONB)
└── OrderLine
    ├── product_variant_id
    ├── quantity
    ├── price, total
```

**✅ Good structure**

**❌ Missing:**
- Order tags/labels
- Fulfillment tracking (multi-fulfillment)
- Order timeline/history
- Risk analysis
- Customer notes vs merchant notes
- Order source (web/mobile/pos/api)
- Test orders flag
- Reference to original cart
- Returns/RMA tracking

---

### PrestaShop - Orders
```sql
ps_orders
├── id_carrier (shipping)
├── id_lang, id_currency
├── id_cart (reference)
├── current_state (workflow)
├── payment
├── module (payment module)
├── total_paid, total_paid_real
├── total_products, total_products_wt
├── total_shipping, total_wrapping
├── total_discounts, total_discounts_tax_incl
├── conversion_rate (currency at order time)
├── valid (validated order)
├── reference (order number)
└── Advanced:
    ├── ps_order_detail (lines)
    ├── ps_order_history (state changes)
    ├── ps_order_carrier (shipping tracking)
    ├── ps_order_return (RMA)
    ├── ps_order_payment (payments)
    ├── ps_order_invoice (invoices)
    ├── ps_order_slip (credit notes)
```

**Features:**
- **Order States** (workflow engine)
- **Order History** (state changes)
- **Multiple Invoices** (partial invoicing)
- **Multiple Payments** (split payments)
- **Returns/RMA** (full system)
- **Credit Notes** (refunds)
- **Conversion Rate** (currency lock)

**Cartino Needs:**
```php
// Order workflow
OrderState
├── name, color, code
├── is_paid, is_shipped, is_delivered
├── is_cancelled, is_refunded
├── send_email, email_template

OrderHistory
├── order_id
├── state_id
├── user_id (who changed)
├── notes
├── created_at

// Fulfillments
OrderFulfillment
├── order_id
├── tracking_number, tracking_url
├── carrier_id
├── shipped_at, delivered_at
├── items (JSONB)

// Returns
OrderReturn
├── order_id
├── return_number
├── status
├── items (JSONB)
├── reason
├── refund_amount

// Invoices
OrderInvoice
├── order_id
├── invoice_number
├── pdf_path
├── amount
├── issued_at

orders
├── source (web/mobile/pos/api/manual)
├── is_test
├── cart_id
├── risk_level
├── customer_note
├── merchant_note
├── tags (JSONB)
```

---

### Shopware - Orders
```php
order
├── orderNumber, orderDateTime
├── stateId (state machine)
├── salesChannelId
├── currencyId, currencyFactor
├── price (object with calculations)
├── amountTotal, amountNet
├── positionPrice, shippingCosts
├── taxStatus (gross/net)
└── Advanced:
    ├── orderCustomer (snapshot)
    ├── deliveries (fulfillments)
    ├── transactions (payments)
    ├── documents (invoices, credit notes)
    ├── tags
```

**Features:**
- **State Machine** (customizable workflow)
- **Customer Snapshot** (data at order time)
- **Multiple Deliveries** (partial shipping)
- **Multiple Transactions** (split payment)
- **Document Generation** (PDF invoices)
- **Tags** for organization

**Cartino Should Add:**
```php
// State machine
orders
├── state_machine_state_id

OrderStateMachine
├── technical_name
├── states (JSONB)
├── transitions (JSONB)

// Customer snapshot
orders
├── customer_snapshot (JSONB)
    {
      email, name, phone,
      billing_address, shipping_address,
      customer_group, tags
    }

// Multiple deliveries
OrderDelivery
├── order_id
├── state_id
├── tracking_number
├── carrier_id
├── items (JSONB)
├── shipped_at
```

---

### Shopify - Orders
```graphql
Order {
  id, name (order number)
  createdAt, processedAt
  test (is test order)
  confirmed, closed, cancelled
  cancelledAt, cancelReason

  customer {
    id
    # Customer snapshot included
  }

  fulfillments {
    id, status
    trackingInfo
    createdAt, updatedAt
  }

  transactions {
    id, kind (sale/capture/void/refund)
    status
    amount, gateway
  }

  refunds {
    id
    createdAt
    refundLineItems
  }

  risks {
    level (LOW, MEDIUM, HIGH)
    message
  }

  tags
  note (customer)
  customAttributes
}
```

**Features:**
- **Test orders** flag
- **Confirmed** before processing
- **Risk analysis** built-in
- **Multiple fulfillments**
- **Multiple transactions** (auth, capture, void)
- **Refunds** tracking
- **Custom attributes**

**Cartino Needs:**
```php
orders
├── is_test
├── confirmed_at
├── processed_at
├── cancelled_at, cancel_reason
├── risk_level (low/medium/high)
├── risk_message
├── custom_attributes (JSONB)

OrderRisk
├── order_id
├── level
├── provider (fraud_analyze/manual)
├── message
├── recommendation
```

---

### Sylius - Orders
```php
Order
├── number, state
├── checkoutState (cart/addressed/shipping/payment/completed)
├── paymentState (cart/awaiting/paid/refunded)
├── shippingState (cart/ready/shipped)
├── currencyCode, localeCode
├── checkoutCompletedAt
└── OrderItem
    ├── productName, variantName (snapshot)
    ├── unitPrice
    ├── units (inventory tracking)
```

**Features:**
- **Checkout State** (separate from order state)
- **Payment State** (separate tracking)
- **Shipping State** (separate tracking)
- **Product Snapshot** (name at order time)
- **Inventory Units** (track each unit sold)

**Cartino Should Add:**
```php
orders
├── checkout_state (cart/addressed/payment/completed)
├── payment_state (pending/authorized/paid/refunded)
├── shipping_state (pending/processing/shipped/delivered)
├── checkout_completed_at

// Snapshot product info
order_lines
├── product_name, variant_name (snapshot)
├── product_data (JSONB snapshot)
```

---

## 6. CUSTOMERS & ACCOUNTS

### Cartino (Current)
```php
Customer
├── email, password
├── first_name, last_name
├── phone
├── customer_group_id
├── accepts_marketing
├── tags (JSONB)
├── data (JSONB)
```

**✅ Good base**

**❌ Missing:**
- Customer state (enabled/disabled/verified)
- Email verification
- Tax exempt status
- Store credit
- Customer notes/timeline
- Last order date
- Total spent (denormalized)
- Order count (denormalized)
- Multiple emails (personal/work)
- Company info (B2B)
- VAT number

---

### PrestaShop - Customers
```sql
ps_customer
├── id_gender
├── id_default_group, id_lang, id_shop
├── id_risk (fraud risk)
├── secure_key (unique key)
├── email, passwd
├── last_passwd_gen
├── birthday
├── newsletter, optin
├── active, deleted
├── is_guest
├── note (private merchant note)
├── outstanding_allow_amount (B2B credit limit)
└── Related:
    ├── ps_customer_group (multiple groups)
    ├── ps_customer_thread (support tickets)
```

**Features:**
- **Risk Level** tracking
- **Multiple Groups** membership
- **Credit Limit** (B2B)
- **Support Tickets** integration
- **Guest Checkout** tracking
- **Newsletter** consent
- **Secure Key** (unique identifier)

**Cartino Needs:**
```php
customers
├── state (active/disabled/deleted)
├── email_verified_at
├── is_guest
├── risk_level
├── credit_limit (B2B)
├── outstanding_balance (B2B)
├── tax_exempt
├── newsletter_subscribed_at
├── marketing_consent
├── last_order_at
├── total_spent (cached)
├── order_count (cached)
├── merchant_notes

// Multiple groups
customer_customer_group
├── customer_id
├── customer_group_id
├── is_primary

// Company info (B2B)
customers
├── company_name
├── vat_number
├── tax_id
```

---

### Shopware - Customers
```php
customer
├── email, password
├── active, guest
├── customerNumber (unique ID)
├── salutationId, title
├── birthday, vatIds
├── company, department
├── salesChannelId
├── groupId, defaultPaymentMethodId
├── defaultBillingAddressId, defaultShippingAddressId
├── requestedGroupId (approval workflow)
├── boundSalesChannelId (locked to channel)
```

**Features:**
- **Customer Number** (permanent ID)
- **VAT IDs** array (multiple countries)
- **Company/Department** (B2B)
- **Requested Group** (approval workflow)
- **Bound Sales Channel** (B2B2C)
- **Salutation** (Mr/Mrs/Dr)

**Cartino Needs:**
```php
customers
├── customer_number (unique, permanent)
├── salutation (Mr/Mrs/Dr/Mx)
├── title (Prof/Dr)
├── birthday
├── vat_ids (JSONB array)
├── company, department
├── bound_channel_id (locked to channel)
├── requested_customer_group_id
├── approved_by, approved_at
```

---

### Shopify - Customers
```graphql
Customer {
  id
  email, phone
  state (DISABLED, INVITED, ENABLED, DECLINED)
  verifiedEmail
  taxExempt, taxExemptions
  acceptsMarketing
  acceptsMarketingUpdatedAt

  metafields (custom data)

  tags
  note (merchant note)

  lastOrder
  ordersCount
  totalSpent

  defaultAddress
  addresses (multiple)

  # Shopify Plus
  marketingOptInLevel
  smsMarketingConsent
}
```

**Features:**
- **State Machine** (DISABLED, INVITED, ENABLED)
- **Email Verification**
- **Tax Exempt** status + exemptions
- **SMS Marketing** consent
- **Marketing Opt-in Level**
- **Metafields** (unlimited custom data)
- **Cached Aggregates** (ordersCount, totalSpent)

**Cartino Needs:**
```php
customers
├── state (disabled/invited/enabled/declined)
├── email_verified_at
├── tax_exempt
├── tax_exemptions (JSONB)
├── sms_marketing_consent
├── sms_marketing_consent_at
├── marketing_opt_in_level (single/confirmed/unknown)
├── last_order_at (cached)
├── lifetime_value (cached)
```

---

### Sylius - Customers
```php
Customer
├── email, emailCanonical
├── firstName, lastName
├── gender, birthday, phoneNumber
├── user (optional - for accounts)
├── group
└── ShopUser (optional account)
    ├── username, enabled
    ├── plainPassword, password
    ├── verifiedAt
    ├── passwordResetToken
    ├── roles
```

**Key Concept:**
- **Customer != User** (può esistere senza account)
- **Canonical Email** (normalized)
- **Optional Account** (guest vs registered)

**Cartino Could Add:**
```php
// Separate Customer from User
customers
├── id
├── email, email_canonical (lowercase)
├── user_id (nullable - guest customers)

users
├── id
├── customer_id
├── username, password
├── roles
```

---

## 7. MULTI-STORE / MULTI-CHANNEL

### Cartino (Current)
```php
Site
├── name, domain
├── locale, currency
├── data (JSONB)

Channel
├── name, handle
├── type (web/mobile/pos)
```

**✅ Basic support**

**❌ Missing:**
- Channel-specific pricing
- Channel-specific inventory
- Channel-specific product availability
- Sales channel settings
- Channel-specific taxes
- Channel-specific shipping

---

### PrestaShop - Multi-Store
```sql
ps_shop
├── id_shop_group
├── name, color (UI)
├── active, deleted
├── id_category (root category)
├── id_theme

ps_shop_group
├── name
├── share_customer, share_order
├── share_stock (shared inventory)

# Per-entity multi-store
ps_product_shop
├── id_product, id_shop
├── price, active, visibility
├── (override per shop)

ps_category_shop
ps_carrier_shop
ps_tax_shop
# ... everything can be per-shop
```

**Features:**
- **Shop Groups** (shared data)
- **Share Settings** (customers, orders, stock)
- **Per-Shop Overrides** (price, visibility, etc.)
- **Root Category** per shop

**Cartino Needs:**
```php
// Site groups
SiteGroup
├── name
├── share_customers
├── share_inventory
├── share_pricing

sites
├── site_group_id
├── root_category_id

// Product availability per site
product_site
├── product_id
├── site_id
├── is_available
├── price_override
├── stock_override
```

---

### Shopware - Sales Channels
```php
sales_channel
├── name, type (storefront/headless/api)
├── languageId, currencyId, countryId
├── paymentMethodIds, shippingMethodIds
├── navigationCategoryId (root)
├── configuration (JSONB)
├── domains (multiple URLs)
└── Channel-specific:
    ├── ProductVisibility
    ├── PromotionSalesChannel
    ├── CustomerSalesChannel
```

**Features:**
- **Channel Types** (storefront/headless/api/product-comparison)
- **Multiple Domains** per channel
- **Configuration** per channel (theme, layout, etc.)
- **Product Visibility** per channel
- **Customer Assignment** to channel

**Cartino Needs:**
```php
channels
├── type (web/mobile/pos/api/marketplace)
├── language_id, currency_id, country_id
├── payment_methods (JSONB)
├── shipping_methods (JSONB)
├── root_category_id
├── configuration (JSONB)

// Domains
channel_domain
├── channel_id
├── domain
├── is_primary

// Product visibility
product_channel
├── product_id
├── channel_id
├── is_visible
├── published_at
```

---

### Shopify - Publications
```graphql
Publication {
  id, name
  supportsFuturePublishing

  products {
    publishedAt
    publishedOnPublication
  }
}

# Channels include:
- Online Store
- Facebook Shop
- Instagram Shopping
- Buy Button
- Google Shopping
- Amazon
- eBay
```

**Features:**
- **Publications** (where to sell)
- **Future Publishing** (schedule per channel)
- **Third-party Channels** (Amazon, eBay)

**Cartino Needs:**
```php
Publication
├── name, type
├── channel_id
├── supports_future_publishing
├── configuration (JSONB)

product_publication
├── product_id
├── publication_id
├── published_at
├── unpublished_at
```

---

### Sylius - Channels
```php
Channel
├── code, name, hostname
├── enabled
├── baseCurrency
├── defaultLocale, locales (multiple)
├── defaultTaxZone
├── taxCalculationStrategy
├── themeName
├── contactEmail
└── Channel-specific:
    ├── ChannelPricing (variant pricing)
    ├── ProductChannels (availability)
    ├── PromotionChannels
```

**Features:**
- **Locales** array (multi-language per channel)
- **Tax Zone** per channel
- **Tax Calculation Strategy** per channel
- **Theme** per channel

**Cartino Needs:**
```php
channels
├── hostname
├── base_currency_id
├── default_locale
├── locales (JSONB)
├── tax_zone_id
├── tax_calculation_strategy
├── theme_name
├── contact_email
```

---

## CONCLUSION & RECOMMENDATIONS

### What Cartino is Doing GREAT ✅

1. **Modern Stack** (Laravel, Inertia, Vue)
2. **Headless-First** architecture
3. **Smart Collections** (Shopify-style)
4. **Price Lists** (B2B-ready)
5. **JSONB Custom Fields** (flexible)
6. **Clean API** design
7. **Asset Management** (Glide on-demand)

---

### TOP PRIORITIES TO ADD 🚀

#### 1. **Product Enhancements** (High Priority)
```php
products
├── handle (permanent URL identifier)
├── compare_at_price (was/now)
├── inventory_policy (deny/continue)
├── min_order_quantity
├── order_increment (multiples)
├── is_closeout (stop when out)
├── cost_price
├── hs_code (customs)
├── condition (new/used/refurbished)

// Product bundles
ProductBundle
├── product_id (bundle product)
├── bundled_product_id
├── quantity

// Product relations
ProductRelation
├── product_id
├── related_product_id
├── type (upsell/cross_sell/related/frequently_bought)
```

#### 2. **Advanced Pricing** (High Priority)
```php
PriceRule
├── name, priority
├── entity_type (product/category/cart)
├── entity_ids (JSONB)
├── conditions (JSONB)
│   ├── customer_group_ids
│   ├── customer_ids
│   ├── country_ids
│   ├── channel_ids
│   ├── min_cart_value
│   ├── min_quantity
├── discount_type (percent/fixed/override)
├── discount_value
├── starts_at, ends_at
├── usage_limit
```

#### 3. **Inventory Management** (Medium Priority)
```php
Warehouse
├── name, code
├── address_id
├── is_active, is_primary
├── priority

Stock
├── product_variant_id
├── warehouse_id
├── quantity_on_hand
├── quantity_reserved
├── quantity_incoming
├── quantity_damaged
├── quantity_available (computed)
├── cost_price

StockMovement
├── stock_id
├── type (sale/purchase/adjustment/transfer/damaged)
├── quantity (delta)
├── order_id, purchase_order_id
├── user_id
├── notes

product_variants
├── restock_days
├── backorder_policy (deny/notify/allow)
```

#### 4. **Order Workflow** (High Priority)
```php
OrderState
├── code (pending/processing/shipped/delivered/cancelled)
├── is_paid, is_shipped, is_delivered, is_cancelled
├── send_email, email_template
├── color (UI)

OrderHistory
├── order_id
├── from_state_id
├── to_state_id
├── user_id
├── notes

OrderFulfillment
├── order_id
├── tracking_number, tracking_url
├── carrier_id
├── items (JSONB)
├── shipped_at, delivered_at

OrderReturn
├── order_id
├── return_number
├── status, reason
├── items (JSONB)
├── refund_amount

orders
├── is_test
├── confirmed_at
├── processed_at
├── risk_level
├── customer_note
├── merchant_note
├── source (web/mobile/pos/api)
├── cart_id
├── customer_snapshot (JSONB)
```

#### 5. **Customer Enhancements** (Medium Priority)
```php
customers
├── customer_number (permanent ID)
├── state (active/disabled/invited/declined)
├── email_verified_at
├── tax_exempt
├── company_name, vat_number
├── credit_limit, outstanding_balance (B2B)
├── risk_level
├── last_order_at
├── lifetime_value (cached)
├── merchant_notes
├── marketing_consent_at
├── sms_marketing_consent_at
```

#### 6. **Channel Management** (Medium Priority)
```php
channels
├── type (web/mobile/pos/api/marketplace)
├── hostname
├── base_currency_id
├── default_locale
├── locales (JSONB)
├── root_category_id
├── configuration (JSONB)

product_channel
├── product_id
├── channel_id
├── is_visible
├── published_at
├── unpublished_at
├── price_override

channel_domain
├── channel_id
├── domain
├── is_primary
```

---

### SUMMARY: FEATURE GAP ANALYSIS

| Feature Category | Cartino | PrestaShop | Shopware | Craft Commerce | Shopify | Sylius | Priority |
|------------------|---------|------------|----------|----------------|---------|--------|----------|
| Product Bundles | ❌ | ✅ | ✅ | ⚠️ | ✅ | ✅ | 🔥 HIGH |
| Digital Products | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ | 🟡 MEDIUM |
| Product Relations | ❌ | ✅ | ✅ | ⚠️ | ✅ | ✅ | 🔥 HIGH |
| Advanced Pricing | ⚠️ | ✅ | ✅ | ❌ | ⚠️ | ✅ | 🔥 HIGH |
| Multi-warehouse | ❌ | ✅ | ⚠️ | ❌ | ✅ | ⚠️ | 🟡 MEDIUM |
| Stock Reservations | ❌ | ✅ | ✅ | ❌ | ✅ | ✅ | 🔥 HIGH |
| Order Workflow | ⚠️ | ✅ | ✅ | ⚠️ | ✅ | ✅ | 🔥 HIGH |
| Fulfillments | ❌ | ✅ | ✅ | ⚠️ | ✅ | ⚠️ | 🔥 HIGH |
| Returns/RMA | ❌ | ✅ | ⚠️ | ❌ | ✅ | ⚠️ | 🟡 MEDIUM |
| Customer B2B | ⚠️ | ✅ | ✅ | ❌ | ⚠️ Plus | ✅ | 🟡 MEDIUM |
| Tax Exempt | ❌ | ✅ | ✅ | ❌ | ✅ | ✅ | 🟡 MEDIUM |
| Channel Pricing | ❌ | ❌ | ✅ | ❌ | ❌ | ✅ | 🔥 HIGH |
| Publications | ❌ | ⚠️ | ⚠️ | ❌ | ✅ | ⚠️ | 🟡 MEDIUM |

---

Vuoi che approfondisca una specifica area o creo migration files per implementare queste feature prioritarie?

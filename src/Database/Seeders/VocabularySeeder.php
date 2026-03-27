<?php

declare(strict_types=1);

namespace Cartino\Database\Seeders;

use Cartino\Models\Vocabulary;
use Illuminate\Database\Seeder;

class VocabularySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Order & Payment
        $this->seedOrderStatuses();
        $this->seedPaymentStatuses();
        $this->seedFulfillmentStatuses();
        $this->seedShippingStatuses();
        $this->seedReturnStatuses();
        $this->seedReturnReasons();

        // Products
        $this->seedProductTypes();
        $this->seedProductRelationTypes();
        $this->seedAttributeTypes();

        // Stock & Inventory
        $this->seedStockStatuses();
        $this->seedStockMovementTypes();
        $this->seedStockReservationStatuses();
        $this->seedStockTransferStatuses();
        $this->seedInventoryLocationTypes();

        // Discounts & Pricing
        $this->seedDiscountTypes();
        $this->seedDiscountTargetTypes();
        $this->seedPricingRuleTypes();

        // Shipping
        $this->seedShippingMethodTypes();
        $this->seedShippingCalculationMethods();

        // Suppliers & Purchase Orders
        $this->seedSupplierStatuses();
        $this->seedPurchaseOrderStatuses();

        // Transactions
        $this->seedTransactionTypes();
        $this->seedTransactionStatuses();

        // Shopping
        $this->seedCartStatuses();
        $this->seedWishlistStatuses();

        // Apps
        $this->seedAppStatuses();
        $this->seedAppInstallationStatuses();
    }

    /**
     * Seed order statuses.
     */
    protected function seedOrderStatuses(): void
    {
        $statuses = [
            [
                'code' => 'pending',
                'labels' => ['en' => 'Pending', 'it' => 'In attesa'],
                'sort_order' => 10,
                'meta' => [
                    'color' => 'orange',
                    'is_final' => false,
                    'allowed_transitions' => ['confirmed', 'cancelled'],
                ],
                'is_system' => true,
            ],
            [
                'code' => 'confirmed',
                'labels' => ['en' => 'Confirmed', 'it' => 'Confermato'],
                'sort_order' => 20,
                'meta' => [
                    'color' => 'blue',
                    'is_final' => false,
                    'allowed_transitions' => ['processing', 'cancelled'],
                ],
                'is_system' => true,
            ],
            [
                'code' => 'processing',
                'labels' => ['en' => 'Processing', 'it' => 'In lavorazione'],
                'sort_order' => 30,
                'meta' => [
                    'color' => 'purple',
                    'is_final' => false,
                    'allowed_transitions' => ['shipped', 'cancelled'],
                ],
                'is_system' => true,
            ],
            [
                'code' => 'shipped',
                'labels' => ['en' => 'Shipped', 'it' => 'Spedito'],
                'sort_order' => 40,
                'meta' => [
                    'color' => 'indigo',
                    'is_final' => false,
                    'allowed_transitions' => ['delivered', 'returned'],
                ],
                'is_system' => true,
            ],
            [
                'code' => 'delivered',
                'labels' => ['en' => 'Delivered', 'it' => 'Consegnato'],
                'sort_order' => 50,
                'meta' => [
                    'color' => 'green',
                    'is_final' => true,
                    'allowed_transitions' => ['returned'],
                ],
                'is_system' => true,
            ],
            [
                'code' => 'cancelled',
                'labels' => ['en' => 'Cancelled', 'it' => 'Annullato'],
                'sort_order' => 60,
                'meta' => [
                    'color' => 'red',
                    'is_final' => true,
                    'allowed_transitions' => [],
                ],
                'is_system' => true,
            ],
            [
                'code' => 'refunded',
                'labels' => ['en' => 'Refunded', 'it' => 'Rimborsato'],
                'sort_order' => 70,
                'meta' => [
                    'color' => 'gray',
                    'is_final' => true,
                    'allowed_transitions' => [],
                ],
                'is_system' => true,
            ],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('order_status', $status['code'], $status);
        }
    }

    /**
     * Seed payment statuses.
     */
    protected function seedPaymentStatuses(): void
    {
        $statuses = [
            [
                'code' => 'pending',
                'labels' => ['en' => 'Pending', 'it' => 'In attesa'],
                'sort_order' => 10,
                'meta' => ['color' => 'orange', 'is_final' => false],
                'is_system' => true,
            ],
            [
                'code' => 'authorized',
                'labels' => ['en' => 'Authorized', 'it' => 'Autorizzato'],
                'sort_order' => 20,
                'meta' => ['color' => 'blue', 'is_final' => false],
                'is_system' => true,
            ],
            [
                'code' => 'paid',
                'labels' => ['en' => 'Paid', 'it' => 'Pagato'],
                'sort_order' => 30,
                'meta' => ['color' => 'green', 'is_final' => true],
                'is_system' => true,
            ],
            [
                'code' => 'partially_refunded',
                'labels' => ['en' => 'Partially Refunded', 'it' => 'Parzialmente rimborsato'],
                'sort_order' => 40,
                'meta' => ['color' => 'yellow', 'is_final' => false],
                'is_system' => true,
            ],
            [
                'code' => 'refunded',
                'labels' => ['en' => 'Refunded', 'it' => 'Rimborsato'],
                'sort_order' => 50,
                'meta' => ['color' => 'gray', 'is_final' => true],
                'is_system' => true,
            ],
            [
                'code' => 'failed',
                'labels' => ['en' => 'Failed', 'it' => 'Fallito'],
                'sort_order' => 60,
                'meta' => ['color' => 'red', 'is_final' => true],
                'is_system' => true,
            ],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('payment_status', $status['code'], $status);
        }
    }

    /**
     * Seed fulfillment statuses.
     */
    protected function seedFulfillmentStatuses(): void
    {
        $statuses = [
            [
                'code' => 'pending',
                'labels' => ['en' => 'Pending', 'it' => 'In attesa'],
                'sort_order' => 10,
                'meta' => ['color' => 'orange'],
                'is_system' => true,
            ],
            [
                'code' => 'fulfilled',
                'labels' => ['en' => 'Fulfilled', 'it' => 'Evaso'],
                'sort_order' => 20,
                'meta' => ['color' => 'green'],
                'is_system' => true,
            ],
            [
                'code' => 'partially_fulfilled',
                'labels' => ['en' => 'Partially Fulfilled', 'it' => 'Parzialmente evaso'],
                'sort_order' => 30,
                'meta' => ['color' => 'blue'],
                'is_system' => true,
            ],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('fulfillment_status', $status['code'], $status);
        }
    }

    /**
     * Seed shipping statuses.
     */
    protected function seedShippingStatuses(): void
    {
        $statuses = [
            [
                'code' => 'pending',
                'labels' => ['en' => 'Pending', 'it' => 'In attesa'],
                'sort_order' => 10,
                'meta' => ['color' => 'orange'],
                'is_system' => true,
            ],
            [
                'code' => 'processing',
                'labels' => ['en' => 'Processing', 'it' => 'In preparazione'],
                'sort_order' => 20,
                'meta' => ['color' => 'blue'],
                'is_system' => true,
            ],
            [
                'code' => 'shipped',
                'labels' => ['en' => 'Shipped', 'it' => 'Spedito'],
                'sort_order' => 30,
                'meta' => ['color' => 'indigo'],
                'is_system' => true,
            ],
            [
                'code' => 'in_transit',
                'labels' => ['en' => 'In Transit', 'it' => 'In transito'],
                'sort_order' => 40,
                'meta' => ['color' => 'purple'],
                'is_system' => true,
            ],
            [
                'code' => 'delivered',
                'labels' => ['en' => 'Delivered', 'it' => 'Consegnato'],
                'sort_order' => 50,
                'meta' => ['color' => 'green'],
                'is_system' => true,
            ],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('shipping_status', $status['code'], $status);
        }
    }

    /**
     * Seed return statuses.
     */
    protected function seedReturnStatuses(): void
    {
        $statuses = [
            [
                'code' => 'requested',
                'labels' => ['en' => 'Requested', 'it' => 'Richiesto'],
                'sort_order' => 10,
                'meta' => ['color' => 'orange'],
                'is_system' => true,
            ],
            [
                'code' => 'approved',
                'labels' => ['en' => 'Approved', 'it' => 'Approvato'],
                'sort_order' => 20,
                'meta' => ['color' => 'blue'],
                'is_system' => true,
            ],
            [
                'code' => 'rejected',
                'labels' => ['en' => 'Rejected', 'it' => 'Rifiutato'],
                'sort_order' => 30,
                'meta' => ['color' => 'red'],
                'is_system' => true,
            ],
            [
                'code' => 'in_transit',
                'labels' => ['en' => 'In Transit', 'it' => 'In transito'],
                'sort_order' => 40,
                'meta' => ['color' => 'purple'],
                'is_system' => true,
            ],
            [
                'code' => 'received',
                'labels' => ['en' => 'Received', 'it' => 'Ricevuto'],
                'sort_order' => 50,
                'meta' => ['color' => 'indigo'],
                'is_system' => true,
            ],
            [
                'code' => 'completed',
                'labels' => ['en' => 'Completed', 'it' => 'Completato'],
                'sort_order' => 60,
                'meta' => ['color' => 'green'],
                'is_system' => true,
            ],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('return_status', $status['code'], $status);
        }
    }

    /**
     * Seed product types.
     */
    protected function seedProductTypes(): void
    {
        $types = [
            [
                'code' => 'physical',
                'labels' => ['en' => 'Physical Product', 'it' => 'Prodotto fisico'],
                'sort_order' => 10,
                'meta' => ['requires_shipping' => true, 'has_inventory' => true],
                'is_system' => true,
            ],
            [
                'code' => 'digital',
                'labels' => ['en' => 'Digital Product', 'it' => 'Prodotto digitale'],
                'sort_order' => 20,
                'meta' => ['requires_shipping' => false, 'has_inventory' => false],
                'is_system' => true,
            ],
            [
                'code' => 'service',
                'labels' => ['en' => 'Service', 'it' => 'Servizio'],
                'sort_order' => 30,
                'meta' => ['requires_shipping' => false, 'has_inventory' => false],
                'is_system' => true,
            ],
            [
                'code' => 'subscription',
                'labels' => ['en' => 'Subscription', 'it' => 'Abbonamento'],
                'sort_order' => 40,
                'meta' => ['requires_shipping' => false, 'has_inventory' => false, 'is_recurring' => true],
                'is_system' => true,
            ],
        ];

        foreach ($types as $type) {
            Vocabulary::createOrUpdate('product_type', $type['code'], $type);
        }
    }

    /**
     * Seed stock statuses.
     */
    protected function seedStockStatuses(): void
    {
        $statuses = [
            [
                'code' => 'in_stock',
                'labels' => ['en' => 'In Stock', 'it' => 'Disponibile'],
                'sort_order' => 10,
                'meta' => ['color' => 'green'],
                'is_system' => true,
            ],
            [
                'code' => 'low_stock',
                'labels' => ['en' => 'Low Stock', 'it' => 'Scorte basse'],
                'sort_order' => 20,
                'meta' => ['color' => 'yellow'],
                'is_system' => true,
            ],
            [
                'code' => 'out_of_stock',
                'labels' => ['en' => 'Out of Stock', 'it' => 'Esaurito'],
                'sort_order' => 30,
                'meta' => ['color' => 'red'],
                'is_system' => true,
            ],
            [
                'code' => 'preorder',
                'labels' => ['en' => 'Pre-order', 'it' => 'Pre-ordine'],
                'sort_order' => 40,
                'meta' => ['color' => 'blue'],
                'is_system' => true,
            ],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('stock_status', $status['code'], $status);
        }
    }

    /**
     * Seed return reasons.
     */
    protected function seedReturnReasons(): void
    {
        $reasons = [
            ['code' => 'defective', 'labels' => ['en' => 'Defective', 'it' => 'Difettoso'], 'sort_order' => 10, 'meta' => ['is_vendor_fault' => true], 'is_system' => true],
            ['code' => 'wrong_item', 'labels' => ['en' => 'Wrong Item', 'it' => 'Articolo sbagliato'], 'sort_order' => 20, 'meta' => ['is_vendor_fault' => true], 'is_system' => true],
            ['code' => 'not_as_described', 'labels' => ['en' => 'Not as Described', 'it' => 'Non conforme alla descrizione'], 'sort_order' => 30, 'meta' => ['is_vendor_fault' => true], 'is_system' => true],
            ['code' => 'changed_mind', 'labels' => ['en' => 'Changed Mind', 'it' => 'Cambiato idea'], 'sort_order' => 40, 'meta' => ['is_vendor_fault' => false], 'is_system' => true],
            ['code' => 'damaged', 'labels' => ['en' => 'Damaged', 'it' => 'Danneggiato'], 'sort_order' => 50, 'meta' => ['is_vendor_fault' => true], 'is_system' => true],
            ['code' => 'other', 'labels' => ['en' => 'Other', 'it' => 'Altro'], 'sort_order' => 60, 'meta' => ['is_vendor_fault' => false], 'is_system' => true],
        ];

        foreach ($reasons as $reason) {
            Vocabulary::createOrUpdate('return_reason', $reason['code'], $reason);
        }
    }

    /**
     * Seed product relation types.
     */
    protected function seedProductRelationTypes(): void
    {
        $types = [
            ['code' => 'upsell', 'labels' => ['en' => 'Upsell', 'it' => 'Upsell'], 'sort_order' => 10, 'is_system' => true],
            ['code' => 'cross_sell', 'labels' => ['en' => 'Cross-sell', 'it' => 'Cross-sell'], 'sort_order' => 20, 'is_system' => true],
            ['code' => 'related', 'labels' => ['en' => 'Related', 'it' => 'Correlato'], 'sort_order' => 30, 'is_system' => true],
            ['code' => 'alternative', 'labels' => ['en' => 'Alternative', 'it' => 'Alternativo'], 'sort_order' => 40, 'is_system' => true],
            ['code' => 'accessory', 'labels' => ['en' => 'Accessory', 'it' => 'Accessorio'], 'sort_order' => 50, 'is_system' => true],
        ];

        foreach ($types as $type) {
            Vocabulary::createOrUpdate('product_relation_type', $type['code'], $type);
        }
    }

    /**
     * Seed attribute types.
     */
    protected function seedAttributeTypes(): void
    {
        $types = [
            ['code' => 'text', 'labels' => ['en' => 'Text', 'it' => 'Testo'], 'sort_order' => 10, 'is_system' => true],
            ['code' => 'number', 'labels' => ['en' => 'Number', 'it' => 'Numero'], 'sort_order' => 20, 'is_system' => true],
            ['code' => 'boolean', 'labels' => ['en' => 'Boolean', 'it' => 'Booleano'], 'sort_order' => 30, 'is_system' => true],
            ['code' => 'select', 'labels' => ['en' => 'Select', 'it' => 'Selezione'], 'sort_order' => 40, 'is_system' => true],
            ['code' => 'multiselect', 'labels' => ['en' => 'Multi-select', 'it' => 'Selezione multipla'], 'sort_order' => 50, 'is_system' => true],
            ['code' => 'color', 'labels' => ['en' => 'Color', 'it' => 'Colore'], 'sort_order' => 60, 'is_system' => true],
            ['code' => 'image', 'labels' => ['en' => 'Image', 'it' => 'Immagine'], 'sort_order' => 70, 'is_system' => true],
        ];

        foreach ($types as $type) {
            Vocabulary::createOrUpdate('attribute_type', $type['code'], $type);
        }
    }

    /**
     * Seed stock movement types.
     */
    protected function seedStockMovementTypes(): void
    {
        $types = [
            ['code' => 'purchase', 'labels' => ['en' => 'Purchase', 'it' => 'Acquisto'], 'sort_order' => 10, 'meta' => ['color' => 'success', 'affects_quantity' => 1, 'requires_reference' => true], 'is_system' => true],
            ['code' => 'sale', 'labels' => ['en' => 'Sale', 'it' => 'Vendita'], 'sort_order' => 20, 'meta' => ['color' => 'primary', 'affects_quantity' => -1, 'requires_reference' => true], 'is_system' => true],
            ['code' => 'return', 'labels' => ['en' => 'Return', 'it' => 'Reso'], 'sort_order' => 30, 'meta' => ['color' => 'success', 'affects_quantity' => 1, 'requires_reference' => true], 'is_system' => true],
            ['code' => 'transfer', 'labels' => ['en' => 'Transfer', 'it' => 'Trasferimento'], 'sort_order' => 40, 'meta' => ['color' => 'primary', 'affects_quantity' => 0], 'is_system' => true],
            ['code' => 'adjustment', 'labels' => ['en' => 'Adjustment', 'it' => 'Rettifica'], 'sort_order' => 50, 'meta' => ['color' => 'info', 'affects_quantity' => 0], 'is_system' => true],
            ['code' => 'damage', 'labels' => ['en' => 'Damage', 'it' => 'Danneggiamento'], 'sort_order' => 60, 'meta' => ['color' => 'danger', 'affects_quantity' => -1], 'is_system' => true],
        ];

        foreach ($types as $type) {
            Vocabulary::createOrUpdate('stock_movement_type', $type['code'], $type);
        }
    }

    /**
     * Seed stock reservation statuses.
     */
    protected function seedStockReservationStatuses(): void
    {
        $statuses = [
            ['code' => 'reserved', 'labels' => ['en' => 'Reserved', 'it' => 'Riservato'], 'sort_order' => 10, 'meta' => ['color' => 'warning', 'is_active' => true], 'is_system' => true],
            ['code' => 'fulfilled', 'labels' => ['en' => 'Fulfilled', 'it' => 'Evaso'], 'sort_order' => 20, 'meta' => ['color' => 'success', 'is_finalized' => true], 'is_system' => true],
            ['code' => 'cancelled', 'labels' => ['en' => 'Cancelled', 'it' => 'Annullato'], 'sort_order' => 30, 'meta' => ['color' => 'gray', 'is_finalized' => true], 'is_system' => true],
            ['code' => 'expired', 'labels' => ['en' => 'Expired', 'it' => 'Scaduto'], 'sort_order' => 40, 'meta' => ['color' => 'danger', 'is_finalized' => true], 'is_system' => true],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('stock_reservation_status', $status['code'], $status);
        }
    }

    /**
     * Seed stock transfer statuses.
     */
    protected function seedStockTransferStatuses(): void
    {
        $statuses = [
            ['code' => 'pending', 'labels' => ['en' => 'Pending', 'it' => 'In attesa'], 'sort_order' => 10, 'meta' => ['color' => 'gray', 'can_be_shipped' => true], 'is_system' => true],
            ['code' => 'in_transit', 'labels' => ['en' => 'In Transit', 'it' => 'In transito'], 'sort_order' => 20, 'meta' => ['color' => 'info', 'can_be_received' => true], 'is_system' => true],
            ['code' => 'received', 'labels' => ['en' => 'Received', 'it' => 'Ricevuto'], 'sort_order' => 30, 'meta' => ['color' => 'success', 'is_complete' => true], 'is_system' => true],
            ['code' => 'cancelled', 'labels' => ['en' => 'Cancelled', 'it' => 'Annullato'], 'sort_order' => 40, 'meta' => ['color' => 'danger', 'is_complete' => true], 'is_system' => true],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('stock_transfer_status', $status['code'], $status);
        }
    }

    /**
     * Seed inventory location types.
     */
    protected function seedInventoryLocationTypes(): void
    {
        $types = [
            ['code' => 'warehouse', 'labels' => ['en' => 'Warehouse', 'it' => 'Magazzino'], 'sort_order' => 10, 'is_system' => true],
            ['code' => 'store', 'labels' => ['en' => 'Store', 'it' => 'Negozio'], 'sort_order' => 20, 'is_system' => true],
            ['code' => 'dropship', 'labels' => ['en' => 'Dropship', 'it' => 'Dropshipping'], 'sort_order' => 30, 'is_system' => true],
            ['code' => 'vendor', 'labels' => ['en' => 'Vendor', 'it' => 'Fornitore'], 'sort_order' => 40, 'is_system' => true],
        ];

        foreach ($types as $type) {
            Vocabulary::createOrUpdate('inventory_location_type', $type['code'], $type);
        }
    }

    /**
     * Seed discount types.
     */
    protected function seedDiscountTypes(): void
    {
        $types = [
            ['code' => 'percentage', 'labels' => ['en' => 'Percentage', 'it' => 'Percentuale'], 'sort_order' => 10, 'meta' => ['requires_value' => true], 'is_system' => true],
            ['code' => 'fixed_amount', 'labels' => ['en' => 'Fixed Amount', 'it' => 'Importo fisso'], 'sort_order' => 20, 'meta' => ['requires_value' => true], 'is_system' => true],
            ['code' => 'free_shipping', 'labels' => ['en' => 'Free Shipping', 'it' => 'Spedizione gratuita'], 'sort_order' => 30, 'meta' => ['requires_value' => false], 'is_system' => true],
            ['code' => 'buy_x_get_y', 'labels' => ['en' => 'Buy X Get Y', 'it' => 'Compra X prendi Y'], 'sort_order' => 40, 'meta' => ['requires_value' => false, 'requires_quantity_rules' => true], 'is_system' => true],
        ];

        foreach ($types as $type) {
            Vocabulary::createOrUpdate('discount_type', $type['code'], $type);
        }
    }

    /**
     * Seed discount target types.
     */
    protected function seedDiscountTargetTypes(): void
    {
        $types = [
            ['code' => 'all', 'labels' => ['en' => 'All Products', 'it' => 'Tutti i prodotti'], 'sort_order' => 10, 'meta' => ['requires_selection' => false], 'is_system' => true],
            ['code' => 'specific_products', 'labels' => ['en' => 'Specific Products', 'it' => 'Prodotti specifici'], 'sort_order' => 20, 'meta' => ['requires_selection' => true], 'is_system' => true],
            ['code' => 'specific_collections', 'labels' => ['en' => 'Specific Collections', 'it' => 'Collezioni specifiche'], 'sort_order' => 30, 'meta' => ['requires_selection' => true], 'is_system' => true],
            ['code' => 'categories', 'labels' => ['en' => 'Categories', 'it' => 'Categorie'], 'sort_order' => 40, 'meta' => ['requires_selection' => true], 'is_system' => true],
        ];

        foreach ($types as $type) {
            Vocabulary::createOrUpdate('discount_target_type', $type['code'], $type);
        }
    }

    /**
     * Seed pricing rule types.
     */
    protected function seedPricingRuleTypes(): void
    {
        $types = [
            ['code' => 'percentage', 'labels' => ['en' => 'Percentage', 'it' => 'Percentuale'], 'sort_order' => 10, 'is_system' => true],
            ['code' => 'fixed', 'labels' => ['en' => 'Fixed', 'it' => 'Fisso'], 'sort_order' => 20, 'is_system' => true],
            ['code' => 'bulk', 'labels' => ['en' => 'Bulk', 'it' => 'All\'ingrosso'], 'sort_order' => 30, 'is_system' => true],
            ['code' => 'tiered', 'labels' => ['en' => 'Tiered', 'it' => 'A scaglioni'], 'sort_order' => 40, 'is_system' => true],
            ['code' => 'bogo', 'labels' => ['en' => 'BOGO', 'it' => 'BOGO'], 'sort_order' => 50, 'is_system' => true],
            ['code' => 'conditional', 'labels' => ['en' => 'Conditional', 'it' => 'Condizionale'], 'sort_order' => 60, 'is_system' => true],
        ];

        foreach ($types as $type) {
            Vocabulary::createOrUpdate('pricing_rule_type', $type['code'], $type);
        }
    }

    /**
     * Seed shipping method types.
     */
    protected function seedShippingMethodTypes(): void
    {
        $types = [
            ['code' => 'flat_rate', 'labels' => ['en' => 'Flat Rate', 'it' => 'Tariffa fissa'], 'sort_order' => 10, 'meta' => ['requires_shipping' => true, 'has_fixed_cost' => true], 'is_system' => true],
            ['code' => 'free', 'labels' => ['en' => 'Free', 'it' => 'Gratuita'], 'sort_order' => 20, 'meta' => ['requires_shipping' => true, 'has_fixed_cost' => true], 'is_system' => true],
            ['code' => 'calculated', 'labels' => ['en' => 'Calculated', 'it' => 'Calcolata'], 'sort_order' => 30, 'meta' => ['requires_shipping' => true, 'has_fixed_cost' => false], 'is_system' => true],
            ['code' => 'pickup', 'labels' => ['en' => 'Pickup', 'it' => 'Ritiro'], 'sort_order' => 40, 'meta' => ['requires_shipping' => false, 'has_fixed_cost' => false], 'is_system' => true],
        ];

        foreach ($types as $type) {
            Vocabulary::createOrUpdate('shipping_method_type', $type['code'], $type);
        }
    }

    /**
     * Seed shipping calculation methods.
     */
    protected function seedShippingCalculationMethods(): void
    {
        $methods = [
            ['code' => 'flat_rate', 'labels' => ['en' => 'Flat Rate', 'it' => 'Tariffa fissa'], 'sort_order' => 10, 'is_system' => true],
            ['code' => 'per_item', 'labels' => ['en' => 'Per Item', 'it' => 'Per articolo'], 'sort_order' => 20, 'meta' => ['requires_item_count' => true], 'is_system' => true],
            ['code' => 'weight_based', 'labels' => ['en' => 'Weight Based', 'it' => 'Basato sul peso'], 'sort_order' => 30, 'meta' => ['requires_weight' => true], 'is_system' => true],
            ['code' => 'price_based', 'labels' => ['en' => 'Price Based', 'it' => 'Basato sul prezzo'], 'sort_order' => 40, 'meta' => ['requires_price' => true], 'is_system' => true],
            ['code' => 'carrier_calculated', 'labels' => ['en' => 'Carrier Calculated', 'it' => 'Calcolato dal corriere'], 'sort_order' => 50, 'meta' => ['requires_external_api' => true], 'is_system' => true],
        ];

        foreach ($methods as $method) {
            Vocabulary::createOrUpdate('shipping_calculation_method', $method['code'], $method);
        }
    }

    /**
     * Seed supplier statuses.
     */
    protected function seedSupplierStatuses(): void
    {
        $statuses = [
            ['code' => 'active', 'labels' => ['en' => 'Active', 'it' => 'Attivo'], 'sort_order' => 10, 'meta' => ['color' => 'success', 'can_place_orders' => true], 'is_system' => true],
            ['code' => 'inactive', 'labels' => ['en' => 'Inactive', 'it' => 'Inattivo'], 'sort_order' => 20, 'meta' => ['color' => 'gray', 'can_place_orders' => false], 'is_system' => true],
            ['code' => 'suspended', 'labels' => ['en' => 'Suspended', 'it' => 'Sospeso'], 'sort_order' => 30, 'meta' => ['color' => 'warning', 'can_place_orders' => false], 'is_system' => true],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('supplier_status', $status['code'], $status);
        }
    }

    /**
     * Seed purchase order statuses.
     */
    protected function seedPurchaseOrderStatuses(): void
    {
        $statuses = [
            ['code' => 'draft', 'labels' => ['en' => 'Draft', 'it' => 'Bozza'], 'sort_order' => 10, 'meta' => ['color' => 'gray', 'can_be_edited' => true], 'is_system' => true],
            ['code' => 'sent', 'labels' => ['en' => 'Sent', 'it' => 'Inviato'], 'sort_order' => 20, 'meta' => ['color' => 'info'], 'is_system' => true],
            ['code' => 'confirmed', 'labels' => ['en' => 'Confirmed', 'it' => 'Confermato'], 'sort_order' => 30, 'meta' => ['color' => 'primary', 'can_receive_items' => true], 'is_system' => true],
            ['code' => 'partial', 'labels' => ['en' => 'Partial', 'it' => 'Parziale'], 'sort_order' => 40, 'meta' => ['color' => 'warning', 'can_receive_items' => true], 'is_system' => true],
            ['code' => 'completed', 'labels' => ['en' => 'Completed', 'it' => 'Completato'], 'sort_order' => 50, 'meta' => ['color' => 'success', 'is_complete' => true], 'is_system' => true],
            ['code' => 'cancelled', 'labels' => ['en' => 'Cancelled', 'it' => 'Annullato'], 'sort_order' => 60, 'meta' => ['color' => 'danger', 'is_complete' => true], 'is_system' => true],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('purchase_order_status', $status['code'], $status);
        }
    }

    /**
     * Seed transaction types.
     */
    protected function seedTransactionTypes(): void
    {
        $types = [
            ['code' => 'payment', 'labels' => ['en' => 'Payment', 'it' => 'Pagamento'], 'sort_order' => 10, 'meta' => ['color' => 'success', 'affects_balance' => true], 'is_system' => true],
            ['code' => 'refund', 'labels' => ['en' => 'Refund', 'it' => 'Rimborso'], 'sort_order' => 20, 'meta' => ['color' => 'warning', 'affects_balance' => false], 'is_system' => true],
            ['code' => 'capture', 'labels' => ['en' => 'Capture', 'it' => 'Cattura'], 'sort_order' => 30, 'meta' => ['color' => 'info', 'affects_balance' => true], 'is_system' => true],
            ['code' => 'void', 'labels' => ['en' => 'Void', 'it' => 'Annullamento'], 'sort_order' => 40, 'meta' => ['color' => 'gray', 'affects_balance' => false], 'is_system' => true],
        ];

        foreach ($types as $type) {
            Vocabulary::createOrUpdate('transaction_type', $type['code'], $type);
        }
    }

    /**
     * Seed transaction statuses.
     */
    protected function seedTransactionStatuses(): void
    {
        $statuses = [
            ['code' => 'pending', 'labels' => ['en' => 'Pending', 'it' => 'In attesa'], 'sort_order' => 10, 'meta' => ['color' => 'warning', 'can_be_cancelled' => true], 'is_system' => true],
            ['code' => 'completed', 'labels' => ['en' => 'Completed', 'it' => 'Completato'], 'sort_order' => 20, 'meta' => ['color' => 'success', 'is_finalized' => true, 'can_be_refunded' => true], 'is_system' => true],
            ['code' => 'failed', 'labels' => ['en' => 'Failed', 'it' => 'Fallito'], 'sort_order' => 30, 'meta' => ['color' => 'danger', 'is_finalized' => true], 'is_system' => true],
            ['code' => 'cancelled', 'labels' => ['en' => 'Cancelled', 'it' => 'Annullato'], 'sort_order' => 40, 'meta' => ['color' => 'gray', 'is_finalized' => true], 'is_system' => true],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('transaction_status', $status['code'], $status);
        }
    }

    /**
     * Seed cart statuses.
     */
    protected function seedCartStatuses(): void
    {
        $statuses = [
            ['code' => 'active', 'labels' => ['en' => 'Active', 'it' => 'Attivo'], 'sort_order' => 10, 'meta' => ['color' => 'green'], 'is_system' => true],
            ['code' => 'abandoned', 'labels' => ['en' => 'Abandoned', 'it' => 'Abbandonato'], 'sort_order' => 20, 'meta' => ['color' => 'orange'], 'is_system' => true],
            ['code' => 'converted', 'labels' => ['en' => 'Converted', 'it' => 'Convertito'], 'sort_order' => 30, 'meta' => ['color' => 'blue'], 'is_system' => true],
            ['code' => 'expired', 'labels' => ['en' => 'Expired', 'it' => 'Scaduto'], 'sort_order' => 40, 'meta' => ['color' => 'red'], 'is_system' => true],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('cart_status', $status['code'], $status);
        }
    }

    /**
     * Seed wishlist statuses.
     */
    protected function seedWishlistStatuses(): void
    {
        $statuses = [
            ['code' => 'active', 'labels' => ['en' => 'Active', 'it' => 'Attiva'], 'sort_order' => 10, 'meta' => ['color' => 'green'], 'is_system' => true],
            ['code' => 'inactive', 'labels' => ['en' => 'Inactive', 'it' => 'Inattiva'], 'sort_order' => 20, 'meta' => ['color' => 'gray'], 'is_system' => true],
            ['code' => 'archived', 'labels' => ['en' => 'Archived', 'it' => 'Archiviata'], 'sort_order' => 30, 'meta' => ['color' => 'orange'], 'is_system' => true],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('wishlist_status', $status['code'], $status);
        }
    }

    /**
     * Seed app statuses.
     */
    protected function seedAppStatuses(): void
    {
        $statuses = [
            ['code' => 'draft', 'labels' => ['en' => 'Draft', 'it' => 'Bozza'], 'sort_order' => 10, 'meta' => ['color' => 'gray', 'can_be_edited' => true], 'is_system' => true],
            ['code' => 'pending', 'labels' => ['en' => 'Pending', 'it' => 'In attesa'], 'sort_order' => 20, 'meta' => ['color' => 'warning'], 'is_system' => true],
            ['code' => 'approved', 'labels' => ['en' => 'Approved', 'it' => 'Approvato'], 'sort_order' => 30, 'meta' => ['color' => 'success', 'is_published' => true, 'can_be_installed' => true], 'is_system' => true],
            ['code' => 'rejected', 'labels' => ['en' => 'Rejected', 'it' => 'Rifiutato'], 'sort_order' => 40, 'meta' => ['color' => 'danger', 'can_be_edited' => true], 'is_system' => true],
            ['code' => 'deprecated', 'labels' => ['en' => 'Deprecated', 'it' => 'Deprecato'], 'sort_order' => 50, 'meta' => ['color' => 'orange', 'can_be_installed' => true], 'is_system' => true],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('app_status', $status['code'], $status);
        }
    }

    /**
     * Seed app installation statuses.
     */
    protected function seedAppInstallationStatuses(): void
    {
        $statuses = [
            ['code' => 'active', 'labels' => ['en' => 'Active', 'it' => 'Attivo'], 'sort_order' => 10, 'meta' => ['color' => 'success', 'is_usable' => true], 'is_system' => true],
            ['code' => 'inactive', 'labels' => ['en' => 'Inactive', 'it' => 'Inattivo'], 'sort_order' => 20, 'meta' => ['color' => 'gray', 'can_be_reactivated' => true], 'is_system' => true],
            ['code' => 'suspended', 'labels' => ['en' => 'Suspended', 'it' => 'Sospeso'], 'sort_order' => 30, 'meta' => ['color' => 'warning', 'can_be_reactivated' => true], 'is_system' => true],
            ['code' => 'cancelled', 'labels' => ['en' => 'Cancelled', 'it' => 'Annullato'], 'sort_order' => 40, 'meta' => ['color' => 'danger'], 'is_system' => true],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('app_installation_status', $status['code'], $status);
        }
    }
}

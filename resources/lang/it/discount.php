<?php

return [
    'discount' => 'Sconto',
    'discounts' => 'Sconti',
    'create_discount' => 'Crea Sconto',
    'edit_discount' => 'Modifica Sconto',
    'discount_details' => 'Dettagli Sconto',
    'manage_discounts' => 'Gestisci Sconti',

    'fields' => [
        'name' => 'Nome',
        'description' => 'Descrizione',
        'code' => 'Codice',
        'type' => 'Tipo',
        'value' => 'Valore',
        'minimum_order_amount' => 'Importo Minimo Ordine',
        'maximum_discount_amount' => 'Sconto Massimo',
        'usage_limit' => 'Limite Utilizzo',
        'usage_limit_per_customer' => 'Limite per Cliente',
        'is_enabled' => 'Attivo',
        'starts_at' => 'Data Inizio',
        'expires_at' => 'Data Scadenza',
        'eligible_customers' => 'Clienti Idonei',
        'eligible_products' => 'Prodotti Idonei',
        'usage_count' => 'Utilizzi',
        'created_at' => 'Creato il',
        'updated_at' => 'Aggiornato il',
    ],

    'types' => [
        'percentage' => 'Percentuale',
        'fixed_amount' => 'Importo Fisso',
        'free_shipping' => 'Spedizione Gratuita',
    ],

    'status' => [
        'active' => 'Attivo',
        'inactive' => 'Inattivo',
        'expired' => 'Scaduto',
        'upcoming' => 'In Arrivo',
    ],

    'messages' => [
        'created_successfully' => 'Sconto creato con successo.',
        'updated_successfully' => 'Sconto aggiornato con successo.',
        'deleted_successfully' => 'Sconto eliminato con successo.',
        'enabled_successfully' => 'Sconto attivato con successo.',
        'disabled_successfully' => 'Sconto disattivato con successo.',
        'duplicated_successfully' => 'Sconto duplicato con successo.',
        'delete_failed' => 'Impossibile eliminare il sconto.',
        'code_not_found' => 'Codice sconto non trovato.',
        'code_inactive' => 'Codice sconto non attivo.',
        'code_valid' => 'Codice sconto valido.',
        'usage_limit_exceeded' => 'Limite di utilizzo superato.',
        'minimum_order_not_met' => 'Importo minimo ordine non raggiunto.',
        'not_eligible' => 'Non idoneo per questo sconto.',
        'applied_successfully' => 'Sconto applicato con successo.',
        'removed_successfully' => 'Sconto rimosso con successo.',
    ],

    'validation' => [
        'name_required' => 'Il nome è obbligatorio.',
        'code_unique' => 'Questo codice è già in uso.',
        'code_format' => 'Il codice può contenere solo lettere e numeri.',
        'type_required' => 'Il tipo di sconto è obbligatorio.',
        'type_invalid' => 'Tipo di sconto non valido.',
        'value_required' => 'Il valore è obbligatorio.',
        'value_numeric' => 'Il valore deve essere un numero.',
        'percentage_max' => 'La percentuale non può superare il 100%.',
        'expires_after_starts' => 'La data di scadenza deve essere posteriore alla data di inizio.',
        'customer_not_found' => 'Cliente non trovato.',
        'product_not_found' => 'Prodotto non trovato.',
    ],

    'placeholders' => [
        'search' => 'Cerca per nome o codice...',
        'name' => 'Es. Sconto Estate 2024',
        'description' => 'Descrizione opzionale del sconto',
        'code' => 'Es. ESTATE2024 (lascia vuoto per generazione automatica)',
        'value' => 'Es. 10 per 10% o 5.00 per €5',
        'minimum_order_amount' => 'Es. 50.00',
        'maximum_discount_amount' => 'Es. 20.00',
        'usage_limit' => 'Es. 100 (lascia vuoto per illimitato)',
        'usage_limit_per_customer' => 'Es. 1 (lascia vuoto per illimitato)',
    ],

    'actions' => [
        'create' => 'Crea Sconto',
        'edit' => 'Modifica',
        'delete' => 'Elimina',
        'enable' => 'Attiva',
        'disable' => 'Disattiva',
        'duplicate' => 'Duplica',
        'view' => 'Visualizza',
        'apply' => 'Applica',
        'remove' => 'Rimuovi',
        'validate_code' => 'Valida Codice',
    ],

    'statistics' => [
        'total_discounts' => 'Totale Sconti',
        'active_discounts' => 'Sconti Attivi',
        'total_applications' => 'Totale Applicazioni',
        'total_discount_amount' => 'Importo Totale Scontato',
        'usage_percentage' => 'Percentuale Utilizzo',
        'unique_customers' => 'Clienti Unici',
        'applications_count' => 'Applicazioni',
        'discount_amount' => 'Importo Scontato',
    ],

    'filters' => [
        'all' => 'Tutti',
        'active' => 'Attivi',
        'inactive' => 'Inattivi',
        'expired' => 'Scaduti',
        'by_type' => 'Per Tipo',
        'by_status' => 'Per Stato',
    ],

    'confirmations' => [
        'delete' => 'Sei sicuro di voler eliminare questo sconto?',
        'disable' => 'Sei sicuro di voler disattivare questo sconto?',
        'enable' => 'Sei sicuro di voler attivare questo sconto?',
    ],

    'empty_states' => [
        'no_discounts' => 'Nessun sconto trovato.',
        'no_applications' => 'Questo sconto non è stato ancora utilizzato.',
        'no_eligible_customers' => 'Nessun cliente idoneo specificato.',
        'no_eligible_products' => 'Nessun prodotto idoneo specificato.',
    ],
];

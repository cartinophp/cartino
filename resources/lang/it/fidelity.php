<?php

return [
    'title' => 'Carta Fedeltà',
    'card_number' => 'Numero Carta',
    'points' => 'Punti',
    'available_points' => 'Punti Disponibili',
    'total_points' => 'Punti Totali',
    'total_earned' => 'Totale Guadagnati',
    'total_redeemed' => 'Totale Riscattati',
    'total_spent' => 'Totale Speso',
    'points_value' => 'Valore Punti',
    'current_tier' => 'Livello Attuale',
    'next_tier' => 'Prossimo Livello',
    'amount_needed' => 'Importo Necessario',
    'is_active' => 'Attiva',
    'issued_at' => 'Emessa il',
    'last_activity' => 'Ultima Attività',

    'transactions' => [
        'title' => 'Transazioni',
        'type' => 'Tipo',
        'points' => 'Punti',
        'description' => 'Descrizione',
        'date' => 'Data',
        'expires_at' => 'Scade il',
        'expired' => 'Scaduto',

        'types' => [
            'earned' => 'Guadagnati',
            'redeemed' => 'Riscattati',
            'expired' => 'Scaduti',
            'adjusted' => 'Aggiustamento',
        ],
    ],

    'tiers' => [
        'bronze' => 'Bronzo',
        'silver' => 'Argento',
        'gold' => 'Oro',
        'platinum' => 'Platino',
    ],

    'actions' => [
        'create_card' => 'Crea Carta',
        'add_points' => 'Aggiungi Punti',
        'redeem_points' => 'Riscatta Punti',
        'view_transactions' => 'Visualizza Transazioni',
        'calculate_points' => 'Calcola Punti',
        'expire_points' => 'Scadi Punti',
    ],

    'messages' => [
        'card_created' => 'Carta fedeltà creata con successo',
        'card_updated' => 'Carta fedeltà aggiornata con successo',
        'points_added' => 'Punti aggiunti con successo',
        'points_redeemed' => 'Punti riscattati con successo',
        'points_expired' => 'Punti scaduti processati',
        'insufficient_points' => 'Punti insufficienti per il riscatto',
        'minimum_points_required' => 'Numero minimo di punti richiesto: :min',
        'card_not_found' => 'Carta fedeltà non trovata',
        'card_already_exists' => 'Carta fedeltà già esistente',
        'system_disabled' => 'Sistema fedeltà disabilitato',
        'points_system_disabled' => 'Sistema punti disabilitato',
    ],

    'validation' => [
        'card_number_required' => 'Il numero della carta è obbligatorio',
        'card_number_invalid' => 'Numero della carta non valido',
        'points_required' => 'Il numero di punti è obbligatorio',
        'points_numeric' => 'I punti devono essere numerici',
        'points_min' => 'I punti devono essere almeno :min',
        'reason_required' => 'La ragione è obbligatoria',
        'reason_max' => 'La ragione non può superare :max caratteri',
    ],

    'statistics' => [
        'total_cards' => 'Totale Carte',
        'active_cards' => 'Carte Attive',
        'points_issued' => 'Punti Emessi',
        'points_redeemed' => 'Punti Riscattati',
        'points_available' => 'Punti Disponibili',
        'amount_spent' => 'Importo Speso',
        'new_cards_month' => 'Nuove Carte (Mese)',
        'points_earned_month' => 'Punti Guadagnati (Mese)',
        'points_redeemed_month' => 'Punti Riscattati (Mese)',
        'expiring_points' => 'Punti in Scadenza',
    ],

    'configuration' => [
        'title' => 'Configurazione Fedeltà',
        'system_enabled' => 'Sistema Abilitato',
        'points_enabled' => 'Punti Abilitati',
        'card_prefix' => 'Prefisso Carta',
        'card_length' => 'Lunghezza Carta',
        'currency_base' => 'Valuta Base',
        'expiration_enabled' => 'Scadenza Abilitata',
        'expiration_months' => 'Mesi Scadenza',
        'min_redemption' => 'Riscatto Minimo',
        'points_rate' => 'Tasso Conversione',
        'tiers' => 'Scaglioni',
    ],

    'emails' => [
        'expiring_points_subject' => 'I tuoi punti fedeltà stanno per scadere',
        'expiring_points_message' => 'Hai :points punti che scadranno il :date. Usali prima che scadano!',
        'points_earned_subject' => 'Hai guadagnato punti fedeltà!',
        'points_earned_message' => 'Hai guadagnato :points punti dal tuo ordine #:order_number.',
        'welcome_card_subject' => 'Benvenuto nel nostro programma fedeltà!',
        'welcome_card_message' => 'La tua carta fedeltà :card_number è pronta. Inizia a guadagnare punti con ogni acquisto!',
    ],
];

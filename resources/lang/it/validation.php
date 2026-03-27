<?php

return [
    'menu' => [
        'title' => [
            'required' => 'Il titolo del menu è obbligatorio.',
            'string' => 'Il titolo del menu deve essere una stringa.',
            'max' => 'Il titolo del menu non può superare :max caratteri.',
        ],
        'handle' => [
            'string' => 'L\'handle del menu deve essere una stringa.',
            'max' => 'L\'handle del menu non può superare :max caratteri.',
            'unique' => 'Un menu con questo handle esiste già.',
        ],
        'description' => [
            'string' => 'La descrizione deve essere una stringa.',
        ],
        'settings' => [
            'array' => 'Le impostazioni devono essere un array.',
        ],
        'is_active' => [
            'boolean' => 'Lo stato attivo deve essere vero o falso.',
        ],
    ],

    'address' => [
        'customer_id' => [
            'required' => 'Il campo cliente è obbligatorio.',
            'exists' => 'Il cliente selezionato non esiste.',
        ],
        'type' => [
            'required' => 'Il tipo di indirizzo è obbligatorio.',
            'enum' => 'Il tipo di indirizzo deve essere fatturazione o spedizione.',
        ],
        'first_name' => [
            'required' => 'Il nome è obbligatorio.',
            'string' => 'Il nome deve essere una stringa.',
            'max' => 'Il nome non può superare :max caratteri.',
        ],
        'last_name' => [
            'required' => 'Il cognome è obbligatorio.',
            'string' => 'Il cognome deve essere una stringa.',
            'max' => 'Il cognome non può superare :max caratteri.',
        ],
        'company' => [
            'string' => 'L\'azienda deve essere una stringa.',
            'max' => 'L\'azienda non può superare :max caratteri.',
        ],
        'address_line_1' => [
            'required' => 'La prima riga dell\'indirizzo è obbligatoria.',
            'string' => 'La prima riga dell\'indirizzo deve essere una stringa.',
            'max' => 'La prima riga dell\'indirizzo non può superare :max caratteri.',
        ],
        'address_line_2' => [
            'string' => 'La seconda riga dell\'indirizzo deve essere una stringa.',
            'max' => 'La seconda riga dell\'indirizzo non può superare :max caratteri.',
        ],
        'city' => [
            'required' => 'La città è obbligatoria.',
            'string' => 'La città deve essere una stringa.',
            'max' => 'La città non può superare :max caratteri.',
        ],
        'state' => [
            'string' => 'La provincia deve essere una stringa.',
            'max' => 'La provincia non può superare :max caratteri.',
        ],
        'postal_code' => [
            'required' => 'Il codice postale è obbligatorio.',
            'string' => 'Il codice postale deve essere una stringa.',
            'max' => 'Il codice postale non può superare :max caratteri.',
        ],
        'country_code' => [
            'required' => 'Il codice paese è obbligatorio.',
            'string' => 'Il codice paese deve essere una stringa.',
            'size' => 'Il codice paese deve essere esattamente di :size caratteri.',
        ],
        'phone' => [
            'string' => 'Il telefono deve essere una stringa.',
            'max' => 'Il telefono non può superare :max caratteri.',
        ],
        'is_default' => [
            'boolean' => 'Lo stato predefinito deve essere vero o falso.',
        ],
    ],

    'wishlist' => [
        'customer_id' => [
            'required' => 'Il campo cliente è obbligatorio.',
            'exists' => 'Il cliente selezionato non esiste.',
        ],
        'name' => [
            'required' => 'Il nome della wishlist è obbligatorio.',
            'string' => 'Il nome della wishlist deve essere una stringa.',
            'max' => 'Il nome della wishlist non può superare :max caratteri.',
        ],
        'description' => [
            'string' => 'La descrizione deve essere una stringa.',
            'max' => 'La descrizione non può superare :max caratteri.',
        ],
        'status' => [
            'enum' => 'Lo stato deve essere uno stato valido della wishlist.',
        ],
        'is_shared' => [
            'boolean' => 'Lo stato condiviso deve essere vero o falso.',
        ],
    ],

    'cart' => [
        'session_id' => [
            'string' => 'L\'ID sessione deve essere una stringa.',
            'max' => 'L\'ID sessione non può superare :max caratteri.',
        ],
        'customer_id' => [
            'exists' => 'Il cliente selezionato non esiste.',
        ],
        'email' => [
            'email' => 'L\'email deve essere un indirizzo email valido.',
            'max' => 'L\'email non può superare :max caratteri.',
        ],
        'status' => [
            'enum' => 'Lo stato deve essere uno stato valido del carrello.',
        ],
        'items' => [
            'array' => 'Gli articoli devono essere un array.',
            'product_id' => [
                'required' => 'L\'ID prodotto è obbligatorio per ogni articolo.',
                'exists' => 'Il prodotto selezionato non esiste.',
            ],
            'quantity' => [
                'required' => 'La quantità è obbligatoria per ogni articolo.',
                'integer' => 'La quantità deve essere un numero intero.',
                'min' => 'La quantità deve essere almeno :min.',
                'max' => 'La quantità non può superare :max.',
            ],
            'price' => [
                'required' => 'Il prezzo è obbligatorio per ogni articolo.',
                'numeric' => 'Il prezzo deve essere un numero.',
                'min' => 'Il prezzo deve essere almeno :min.',
            ],
        ],
        'subtotal' => [
            'numeric' => 'Il subtotale deve essere un numero.',
            'min' => 'Il subtotale deve essere almeno :min.',
        ],
        'tax_amount' => [
            'numeric' => 'L\'importo delle tasse deve essere un numero.',
            'min' => 'L\'importo delle tasse deve essere almeno :min.',
        ],
        'shipping_amount' => [
            'numeric' => 'L\'importo della spedizione deve essere un numero.',
            'min' => 'L\'importo della spedizione deve essere almeno :min.',
        ],
        'discount_amount' => [
            'numeric' => 'L\'importo dello sconto deve essere un numero.',
            'min' => 'L\'importo dello sconto deve essere almeno :min.',
        ],
        'total_amount' => [
            'numeric' => 'L\'importo totale deve essere un numero.',
            'min' => 'L\'importo totale deve essere almeno :min.',
        ],
        'currency' => [
            'string' => 'La valuta deve essere una stringa.',
            'size' => 'La valuta deve essere esattamente di :size caratteri.',
        ],
        'shipping_address' => [
            'array' => 'L\'indirizzo di spedizione deve essere un array.',
        ],
        'billing_address' => [
            'array' => 'L\'indirizzo di fatturazione deve essere un array.',
        ],
        'metadata' => [
            'array' => 'I metadati devono essere un array.',
        ],
    ],

    'stock_notification' => [
        'user_id' => [
            'required' => 'Il campo utente è obbligatorio.',
            'exists' => 'L\'utente selezionato non esiste.',
        ],
        'product_id' => [
            'required' => 'Il campo prodotto è obbligatorio.',
            'exists' => 'Il prodotto selezionato non esiste.',
        ],
        'email' => [
            'required' => 'L\'email è obbligatoria.',
            'email' => 'L\'email deve essere un indirizzo email valido.',
            'max' => 'L\'email non può superare :max caratteri.',
        ],
        'status' => [
            'enum' => 'Lo stato deve essere uno stato valido della notifica.',
        ],
    ],
];

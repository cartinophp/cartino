<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Linee linguistiche per l'autenticazione sociale
    |--------------------------------------------------------------------------
    |
    | Le seguenti linee linguistiche sono utilizzate durante l'autenticazione
    | sociale per vari messaggi che dobbiamo mostrare all'utente. Sei libero
    | di modificare queste linee secondo i requisiti della tua applicazione.
    |
    */

    'providers' => [
        'google' => 'Google',
        'facebook' => 'Facebook',
        'twitter' => 'Twitter',
        'github' => 'GitHub',
        'linkedin' => 'LinkedIn',
        'apple' => 'Apple',
        'discord' => 'Discord',
        'microsoft' => 'Microsoft',
    ],

    'actions' => [
        'login_with' => 'Accedi con :provider',
        'register_with' => 'Registrati con :provider',
        'connect_with' => 'Collega :provider',
        'link_account' => 'Collega Account',
        'unlink_account' => 'Scollega Account',
        'continue_with' => 'Continua con :provider',
        'sign_in_with' => 'Accedi con :provider',
    ],

    'messages' => [
        'success' => [
            'login' => 'Accesso effettuato con successo tramite :provider!',
            'register' => 'Account creato e accesso effettuato tramite :provider!',
            'linked' => 'Account :provider collegato con successo!',
            'unlinked' => 'Account :provider scollegato con successo!',
            'updated' => 'Il tuo account :provider è stato aggiornato!',
        ],

        'errors' => [
            'provider_not_supported' => 'Il provider :provider non è supportato.',
            'provider_not_configured' => 'Il provider :provider non è configurato correttamente.',
            'authentication_failed' => 'Autenticazione con :provider fallita. Riprova.',
            'email_required' => 'È richiesto un indirizzo email per completare la registrazione.',
            'account_exists' => 'Esiste già un account con questo indirizzo email.',
            'already_linked' => 'Il tuo account è già collegato a :provider.',
            'not_linked' => 'Il tuo account non è collegato a :provider.',
            'cannot_unlink_only' => 'Impossibile scollegare l\'unico metodo di autenticazione. Imposta prima una password.',
            'account_used_elsewhere' => 'Questo account :provider è già collegato ad un altro utente.',
            'linking_failed' => 'Impossibile collegare il tuo account :provider. Riprova.',
            'unlinking_failed' => 'Impossibile scollegare il tuo account :provider. Riprova.',
            'callback_error' => 'Si è verificato un errore durante l\'autenticazione :provider.',
            'state_mismatch' => 'Stato di autenticazione non valido. Riprova.',
            'access_denied' => 'Accesso negato da :provider.',
            'cancelled' => 'Autenticazione annullata.',
        ],

        'warnings' => [
            'email_not_verified' => 'La tua email :provider non è verificata.',
            'limited_permissions' => 'Permessi limitati concessi da :provider.',
            'data_incomplete' => 'Alcuni dati del profilo da :provider non sono stati recuperati.',
        ],
    ],

    'buttons' => [
        'login' => 'Accedi',
        'register' => 'Registrati',
        'link' => 'Collega',
        'unlink' => 'Scollega',
        'cancel' => 'Annulla',
        'try_again' => 'Riprova',
        'back_to_login' => 'Torna al Login',
    ],

    'labels' => [
        'connected_accounts' => 'Account Collegati',
        'available_providers' => 'Metodi di Accesso Disponibili',
        'no_connected_accounts' => 'Nessun account collegato',
        'primary_provider' => 'Metodo di Accesso Primario',
        'linked_on' => 'Collegato il :date',
        'last_used' => 'Ultimo utilizzo :date',
        'account_status' => 'Stato Account',
        'verified' => 'Verificato',
        'unverified' => 'Non verificato',
    ],

    'descriptions' => [
        'link_account' => 'Collega il tuo account :provider per abilitare l\'accesso rapido e accedere a funzionalità aggiuntive.',
        'unlink_account' => 'Rimuovi la connessione al tuo account :provider. Puoi sempre ricollegarlo in seguito.',
        'primary_method' => 'Questo è il tuo metodo di accesso principale. Puoi comunque utilizzare altri account collegati per accedere.',
        'security_notice' => 'Collegare gli account aiuta a proteggere il tuo profilo e fornisce opzioni di accesso di backup.',
        'data_usage' => 'Accediamo solo alle informazioni di base del profilo da :provider per personalizzare la tua esperienza.',
    ],

    'status' => [
        'connecting' => 'Connessione a :provider...',
        'redirecting' => 'Reindirizzamento a :provider...',
        'processing' => 'Elaborazione autenticazione...',
        'linking' => 'Collegamento account...',
        'unlinking' => 'Scollegamento account...',
        'verifying' => 'Verifica credenziali...',
    ],

];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Linee linguistiche per l'autenticazione
    |--------------------------------------------------------------------------
    |
    | Le seguenti linee linguistiche sono utilizzate durante l'autenticazione
    | per vari messaggi che dobbiamo mostrare all'utente. Sei libero di
    | modificare queste linee secondo i requisiti della tua applicazione.
    |
    */

    'failed' => 'Le credenziali fornite non corrispondono ai nostri record.',
    'password' => 'La password fornita non è corretta.',
    'throttle' => 'Troppi tentativi di accesso. Riprova tra :seconds secondi.',
    'cp_access_denied' => 'Non hai i permessi per accedere al Pannello di Controllo.',
    'logged_out' => 'Sei stato disconnesso con successo.',

    'password_reset_sent' => 'Ti abbiamo inviato il link per il reset della password via email!',
    'password_reset_failed' => 'Non riusciamo a trovare un utente con questo indirizzo email.',
    'password_reset_success' => 'La tua password è stata ripristinata con successo!',
    'password_reset_invalid' => 'Il token per il reset della password non è valido.',
    'password_reset_throttle' => 'Troppi tentativi di reset password. Riprova tra :seconds secondi.',

    'validation' => [
        'email_required' => 'Il campo email è obbligatorio.',
        'email_invalid' => 'L\'email deve essere un indirizzo email valido.',
        'password_required' => 'Il campo password è obbligatorio.',
        'password_confirmed' => 'La conferma della password non corrisponde.',
        'password_min' => 'La password deve contenere almeno :min caratteri.',
    ],

    'labels' => [
        'email' => 'Indirizzo Email',
        'password' => 'Password',
        'password_confirmation' => 'Conferma Password',
        'remember_me' => 'Ricordami',
        'forgot_password' => 'Hai dimenticato la password?',
        'login' => 'Accedi',
        'logout' => 'Esci',
        'send_reset_link' => 'Invia Link Reset Password',
        'reset_password' => 'Reimposta Password',
        'back_to_login' => 'Torna al login',
    ],

    'headings' => [
        'login' => 'Accedi al tuo account',
        'forgot_password' => 'Hai dimenticato la password?',
        'reset_password' => 'Reimposta la tua password',
        'welcome' => 'Benvenuto in :app',
    ],

    'descriptions' => [
        'login' => 'Inserisci le tue credenziali per accedere al Pannello di Controllo.',
        'forgot_password' => 'Nessun problema. Fornisci il tuo indirizzo email e ti invieremo un link per reimpostare la password.',
        'reset_password' => 'Inserisci la tua nuova password qui sotto.',
    ],

    'placeholders' => [
        'email' => 'Inserisci il tuo indirizzo email',
        'password' => 'Inserisci la tua password',
        'password_confirmation' => 'Conferma la tua nuova password',
    ],

    'actions' => [
        'signing_in' => 'Accesso in corso...',
        'sending_link' => 'Invio link...',
        'resetting_password' => 'Reset password...',
    ],

];

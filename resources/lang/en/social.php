<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Social Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during social authentication for
    | various messages that we need to display to the user. You are free to
    | modify these language lines according to your application's requirements.
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
        'login_with' => 'Login with :provider',
        'register_with' => 'Register with :provider',
        'connect_with' => 'Connect :provider',
        'link_account' => 'Link Account',
        'unlink_account' => 'Unlink Account',
        'continue_with' => 'Continue with :provider',
        'sign_in_with' => 'Sign in with :provider',
    ],

    'messages' => [
        'success' => [
            'login' => 'Successfully logged in with :provider!',
            'register' => 'Account created and logged in with :provider!',
            'linked' => 'Successfully linked your :provider account!',
            'unlinked' => 'Successfully unlinked your :provider account!',
            'updated' => 'Your :provider account has been updated!',
        ],

        'errors' => [
            'provider_not_supported' => 'The :provider provider is not supported.',
            'provider_not_configured' => 'The :provider provider is not properly configured.',
            'authentication_failed' => 'Authentication with :provider failed. Please try again.',
            'email_required' => 'An email address is required to complete registration.',
            'account_exists' => 'An account with this email already exists.',
            'already_linked' => 'Your account is already linked to :provider.',
            'not_linked' => 'Your account is not linked to :provider.',
            'cannot_unlink_only' => 'Cannot unlink the only authentication method. Please set a password first.',
            'account_used_elsewhere' => 'This :provider account is already linked to another user.',
            'linking_failed' => 'Failed to link your :provider account. Please try again.',
            'unlinking_failed' => 'Failed to unlink your :provider account. Please try again.',
            'callback_error' => 'An error occurred during :provider authentication.',
            'state_mismatch' => 'Invalid authentication state. Please try again.',
            'access_denied' => 'Access denied by :provider.',
            'cancelled' => 'Authentication was cancelled.',
        ],

        'warnings' => [
            'email_not_verified' => 'Your :provider email is not verified.',
            'limited_permissions' => 'Limited permissions granted by :provider.',
            'data_incomplete' => 'Some profile data from :provider could not be retrieved.',
        ],
    ],

    'buttons' => [
        'login' => 'Login',
        'register' => 'Register',
        'link' => 'Link',
        'unlink' => 'Unlink',
        'cancel' => 'Cancel',
        'try_again' => 'Try Again',
        'back_to_login' => 'Back to Login',
    ],

    'labels' => [
        'connected_accounts' => 'Connected Accounts',
        'available_providers' => 'Available Login Methods',
        'no_connected_accounts' => 'No accounts connected',
        'primary_provider' => 'Primary Login Method',
        'linked_on' => 'Linked on :date',
        'last_used' => 'Last used :date',
        'account_status' => 'Account Status',
        'verified' => 'Verified',
        'unverified' => 'Unverified',
    ],

    'descriptions' => [
        'link_account' => 'Link your :provider account to enable quick sign-in and access additional features.',
        'unlink_account' => 'Remove the connection to your :provider account. You can always link it again later.',
        'primary_method' => 'This is your primary login method. You can still use other linked accounts to sign in.',
        'security_notice' => 'Linking accounts helps secure your profile and provides backup sign-in options.',
        'data_usage' => 'We only access basic profile information from :provider to personalize your experience.',
    ],

    'status' => [
        'connecting' => 'Connecting to :provider...',
        'redirecting' => 'Redirecting to :provider...',
        'processing' => 'Processing authentication...',
        'linking' => 'Linking account...',
        'unlinking' => 'Unlinking account...',
        'verifying' => 'Verifying credentials...',
    ],

];

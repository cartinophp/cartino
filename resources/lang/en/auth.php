<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    'cp_access_denied' => 'You do not have permission to access the Control Panel.',
    'logged_out' => 'You have been successfully logged out.',

    'password_reset_sent' => 'We have emailed your password reset link!',
    'password_reset_failed' => 'We can\'t find a user with that email address.',
    'password_reset_success' => 'Your password has been reset successfully!',
    'password_reset_invalid' => 'This password reset token is invalid.',
    'password_reset_throttle' => 'Too many password reset attempts. Please try again in :seconds seconds.',

    'validation' => [
        'email_required' => 'The email field is required.',
        'email_invalid' => 'The email must be a valid email address.',
        'password_required' => 'The password field is required.',
        'password_confirmed' => 'The password confirmation does not match.',
        'password_min' => 'The password must be at least :min characters.',
    ],

    'labels' => [
        'email' => 'Email Address',
        'password' => 'Password',
        'password_confirmation' => 'Confirm Password',
        'remember_me' => 'Remember me',
        'forgot_password' => 'Forgot your password?',
        'login' => 'Sign In',
        'logout' => 'Sign Out',
        'send_reset_link' => 'Email Password Reset Link',
        'reset_password' => 'Reset Password',
        'back_to_login' => 'Back to login',
    ],

    'headings' => [
        'login' => 'Sign in to your account',
        'forgot_password' => 'Forgot your password?',
        'reset_password' => 'Reset your password',
        'welcome' => 'Welcome to :app',
    ],

    'descriptions' => [
        'login' => 'Enter your credentials to access the Control Panel.',
        'forgot_password' => 'No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.',
        'reset_password' => 'Please enter your new password below.',
    ],

    'placeholders' => [
        'email' => 'Enter your email address',
        'password' => 'Enter your password',
        'password_confirmation' => 'Confirm your new password',
    ],

    'actions' => [
        'signing_in' => 'Signing in...',
        'sending_link' => 'Sending link...',
        'resetting_password' => 'Resetting password...',
    ],

];

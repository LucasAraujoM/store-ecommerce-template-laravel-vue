<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Store identity
    |--------------------------------------------------------------------------
    */

    'name' => env('STORE_NAME', 'Lunaria'),
    'currency' => 'ARS',
    'locale' => 'es_AR',

    /*
    |--------------------------------------------------------------------------
    | Shipping
    |--------------------------------------------------------------------------
    */

    'free_shipping_from_cents' => (int) env('STORE_FREE_SHIPPING_FROM_CENTS', 15000000),
    'shipments_enabled' => (bool) env('STORE_SHIPMENTS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Bank transfer
    |--------------------------------------------------------------------------
    */

    'transfer' => [
        'bank' => env('STORE_TRANSFER_BANK', 'Banco Galicia'),
        'account_name' => env('STORE_TRANSFER_ACCOUNT_NAME', 'Lunaria S.R.L.'),
        'cuit' => env('STORE_TRANSFER_CUIT', '30-71234567-8'),
        'cbu' => env('STORE_TRANSFER_CBU', '0070999430004123456789'),
        'alias' => env('STORE_TRANSFER_ALIAS', 'lunaria.ropa'),
        'discount_percent' => (int) env('STORE_TRANSFER_DISCOUNT_PERCENT', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact
    |--------------------------------------------------------------------------
    */

    'contact' => [
        'email' => env('STORE_CONTACT_EMAIL', 'hola@lunaria.com.ar'),
        'phone' => env('STORE_CONTACT_PHONE', '+54 11 5555 5555'),
        'instagram' => env('STORE_INSTAGRAM', 'lunaria.ropa'),
        'address' => env('STORE_ADDRESS', 'Palermo, Ciudad Autónoma de Buenos Aires'),
    ],

    /*
    |--------------------------------------------------------------------------
    | MercadoPago
    |--------------------------------------------------------------------------
    |
    | The gateway is enabled once an access token is configured. Checkout Pro
    | will be wired in the payments phase; meanwhile the storefront offers
    | bank transfer.
    |
    */

    'mercadopago' => [
        'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
        'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'),
        'sandbox' => (bool) env('MERCADOPAGO_SANDBOX', true),
    ],

];

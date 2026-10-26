<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Gateway
    |--------------------------------------------------------------------------
    */

    'default' => env('PAYMENT_GATEWAY', 'zarinpal'),

    /*
    |--------------------------------------------------------------------------
    | Shared Settings
    |--------------------------------------------------------------------------
    |
    | Orders store `total_amount` in Tomans. Every Iranian gateway below wants
    | Rials, so the base driver multiplies by this factor before sending.
    |
    */

    'toman_to_rial' => 10,

    /*
    | HTTP timeout (seconds) used for every gateway call.
    */
    'timeout' => (int) env('PAYMENT_HTTP_TIMEOUT', 20),

    /*
    | Verification windows are short (IDPay and DigiPay both auto-reverse after
    | 10 minutes). Retry transient network failures this many times.
    */
    'retries' => (int) env('PAYMENT_HTTP_RETRIES', 2),

    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    |
    | `enabled` controls whether the gateway is offered at checkout. Each
    | driver validates its own credentials before it is allowed to run.
    |
    */

    'drivers' => [

        /*
        | ZarinPal — https://www.zarinpal.com/docs/paymentGateway/
        |
        | Auth: a single 36-character merchant_id sent in the JSON body.
        | Sandbox: sandbox.zarinpal.com accepts any 36-character merchant_id.
        */
        'zarinpal' => [
            'enabled' => env('ZARINPAL_ENABLED', true),
            'label' => 'زرین‌پال',
            'description' => 'پرداخت آنلاین با تمامی کارت‌های بانکی',
            'merchant_id' => env('ZARINPAL_MERCHANT_ID', ''),
            'sandbox' => env('ZARINPAL_SANDBOX', true),

            // Optional affiliate/referrer code issued by ZarinPal.
            'referrer_id' => env('ZARINPAL_REFERRER_ID'),

            'endpoints' => [
                'live' => [
                    'request' => 'https://payment.zarinpal.com/pg/v4/payment/request.json',
                    'verify' => 'https://payment.zarinpal.com/pg/v4/payment/verify.json',
                    'unverified' => 'https://payment.zarinpal.com/pg/v4/payment/unVerified.json',
                    'start_pay' => 'https://payment.zarinpal.com/pg/StartPay/',
                ],
                'sandbox' => [
                    'request' => 'https://sandbox.zarinpal.com/pg/v4/payment/request.json',
                    'verify' => 'https://sandbox.zarinpal.com/pg/v4/payment/verify.json',
                    'unverified' => 'https://sandbox.zarinpal.com/pg/v4/payment/unVerified.json',
                    'start_pay' => 'https://sandbox.zarinpal.com/pg/StartPay/',
                ],
            ],
        ],

        /*
        | IDPay — https://idpay.ir/web-service/v1.1/
        |
        | Auth: X-API-KEY header (36 characters). Sandbox is a header flag on
        | the production host, not a separate host.
        */
        'idpay' => [
            'enabled' => env('IDPAY_ENABLED', true),
            'label' => 'آیدی‌پی',
            'description' => 'پرداخت آنلاین با تمامی کارت‌های بانکی',
            'api_key' => env('IDPAY_API_KEY', ''),
            'sandbox' => env('IDPAY_SANDBOX', true),

            'endpoints' => [
                'request' => 'https://api.idpay.ir/v1.1/payment',
                'verify' => 'https://api.idpay.ir/v1.1/payment/verify',
                'inquiry' => 'https://api.idpay.ir/v1.1/payment/inquiry',
            ],
        ],

        /*
        | DigiPay UPG — https://www.mydigipay.com/developers/docs/upg/
        |
        | Auth: OAuth2 password grant. `client_id`/`client_secret` form the
        | HTTP Basic header; `username`/`password` are the form-data body.
        | All four are issued by DigiPay support — there is no single API key.
        */
        'digipay' => [
            'enabled' => env('DIGIPAY_ENABLED', true),
            'label' => 'دیجی‌پی',
            'description' => 'پرداخت با کارت بانکی، کیف پول یا اعتبار دیجی‌پی',
            'client_id' => env('DIGIPAY_CLIENT_ID', ''),
            'client_secret' => env('DIGIPAY_CLIENT_SECRET', ''),
            'username' => env('DIGIPAY_USERNAME', ''),
            'password' => env('DIGIPAY_PASSWORD', ''),
            'sandbox' => env('DIGIPAY_SANDBOX', true),

            // Ticket type 0 is the standard "smart payment gateway" (UPG).
            // Other types (5, 11, 13, …) are BNPL/credit products.
            'ticket_type' => (int) env('DIGIPAY_TICKET_TYPE', 0),

            // Sent as the `Agent` header; WEB for browser checkouts.
            'agent' => env('DIGIPAY_AGENT', 'WEB'),
            'api_version' => '2022-02-02',

            'base_url' => [
                'live' => 'https://api.mydigipay.com/digipay/api',
                'sandbox' => 'https://uat.mydigipay.info/digipay/api',
            ],
        ],

        /*
        | SnappPay (اسنپ‌پی) — instalment / buy-now-pay-later.
        |
        | The merchant spec is issued by SnappPay after the contract is signed;
        | request base_url, username, password, client_id and client_secret from
        | your SnappPay account manager (there is no public sandbox signup).
        | Auth: OAuth2 password grant, client_id:client_secret as HTTP Basic.
        |
        | min_amount / max_amount are the instalment window in Tomans from
        | your contract (0 = no limit). Outside it SnappPay is hidden at
        | checkout. auto_settle verifies and settles in one go — SnappPay
        | reverts a payment that is verified but never settled.
        */
        'snapppay' => [
            'enabled' => env('SNAPPPAY_ENABLED', false),
            'label' => 'اسنپ‌پی',
            'description' => 'پرداخت اقساطی؛ همین حالا بخرید، بعداً پرداخت کنید',
            'base_url' => env('SNAPPPAY_BASE_URL', ''),
            'username' => env('SNAPPPAY_USERNAME', ''),
            'password' => env('SNAPPPAY_PASSWORD', ''),
            'client_id' => env('SNAPPPAY_CLIENT_ID', ''),
            'client_secret' => env('SNAPPPAY_CLIENT_SECRET', ''),
            'scope' => env('SNAPPPAY_SCOPE', 'online-merchant'),
            'auto_settle' => env('SNAPPPAY_AUTO_SETTLE', true),
            'min_amount' => (int) env('SNAPPPAY_MIN_AMOUNT', 0),
            'max_amount' => (int) env('SNAPPPAY_MAX_AMOUNT', 0),
        ],

    ],

];

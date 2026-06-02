<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenFDA API Configuration
    |--------------------------------------------------------------------------
    |
    | Central configuration for all OpenFDA API interactions. The API key is
    | optional — unauthenticated requests are allowed but rate-limited.
    | Authenticated requests: 240 req/min | Unauthenticated: 40 req/min.
    |
    */

    'api_key'  => env('OPENFDA_API_KEY', ''),
    'base_url' => env('OPENFDA_BASE_URL', 'https://api.fda.gov'),

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */

    'cache' => [
        // Default TTL: 72 hours (259200 seconds)
        'ttl'    => env('OPENFDA_CACHE_TTL', 259200),
        // Cache store — uses the app default (database) unless overridden
        'store'  => env('OPENFDA_CACHE_STORE', null),
        // Cache key prefix to namespace all OpenFDA keys
        'prefix' => 'openfda',
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Settings
    |--------------------------------------------------------------------------
    */

    'http' => [
        'timeout'         => env('OPENFDA_TIMEOUT', 15),
        'connect_timeout' => env('OPENFDA_CONNECT_TIMEOUT', 5),
        'retry'           => [
            'times' => 3,
            'sleep' => 200, // ms
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Endpoint Paths
    |--------------------------------------------------------------------------
    */

    'endpoints' => [
        'drug_label'    => '/drug/label.json',
        'drug_event'    => '/drug/event.json',
        'drug_ndc'      => '/drug/ndc.json',
        'drug_shortage' => '/drug/drugsfda.json',
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Defaults
    |--------------------------------------------------------------------------
    */

    'search' => [
        'default_limit'    => 10,
        'max_limit'        => 50,
        'interaction_limit' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interaction Detection
    |--------------------------------------------------------------------------
    |
    | Severity keywords mapped to severity levels used when parsing
    | adverse event / drug interaction narratives.
    |
    */

    'interaction' => [
        'severity_keywords' => [
            'fatal'     => ['fatal', 'death', 'died', 'lethal'],
            'serious'   => ['serious', 'hospitalization', 'life-threatening', 'disability'],
            'moderate'  => ['moderate', 'significant', 'caution', 'monitor'],
            'mild'      => ['mild', 'minor', 'minimal', 'low risk'],
        ],
    ],

];

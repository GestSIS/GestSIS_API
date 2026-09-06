<?php

return [

    /*
     * The log profile which determines whether a request should be logged.
     * It should implement `LogProfile`.
     */
    'log_profile' => \Spatie\HttpLogger\LogNonGetRequests::class,

    /*
     * The log writer used to write the request to a log.
     * It should implement `LogWriter`.
     */
    'log_writer' => \App\Application\Http\Logging\RedactingHttpLogWriter::class,

    /*
     * The log channel used to write the request.
     */
    'log_channel' => env('LOG_CHANNEL', 'stack'),

    /*
     * The log level used to log the request.
     */
    'log_level' => 'info',

    /*
     * Filter out body fields which will never be logged.
     */
    'except' => [
        'password',
        'password_confirmation',
        // Secrets tiers (RTA bearer token, identifiant ASPSMS) : chiffrés en base,
        // ils ne doivent pas se retrouver en clair dans laravel.log.
        'token',
        'username',
        // Données personnelles sensibles des sapeurs / recrues.
        'no_avs',
        'iban',
        'date_naissance',
    ],

    /*
     * List of headers that will be sanitized. For example Authorization, Cookie, Set-Cookie...
     */
    'sanitize_headers' => [
        'Authorization'
    ],
];

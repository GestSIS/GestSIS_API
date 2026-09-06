<?php

namespace App\Application\Http\Logging;

use Illuminate\Http\Request;
use Spatie\HttpLogger\DefaultLogWriter;

/**
 * Log writer HTTP qui masque les secrets portés par l'URI.
 *
 * Le jeton de recrutement voyage dans le chemin (POST /recrutement/{sisKey}/{token})
 * et n'est stocké que haché en base : le laisser en clair dans laravel.log
 * annulerait ce hachage. Les champs de body sensibles sont exclus via
 * `http-logger.except`.
 */
class RedactingHttpLogWriter extends DefaultLogWriter
{
    public function getMessage(Request $request)
    {
        $message = parent::getMessage($request);
        $message['uri'] = preg_replace('#(/recrutement/[^/]+/)[^/?\#]+#', '$1[redacted]', $message['uri']);

        return $message;
    }
}

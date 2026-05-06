<?php

namespace App\Application\Sentry;

use Sentry\Event;
use Sentry\EventHint;

class BeforeSendCallback
{
    /**
     * Liste des champs sensibles à masquer
     */
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'old_password',
        'api_token',
        'token',
        'secret',
        'api_key',
        'access_token',
        'refresh_token',
        'private_key',
        'credit_card',
        'cvv',
        'ssn',
    ];

    /**
     * Nettoie les données sensibles avant l'envoi à Sentry
     *
     * @see https://docs.sentry.io/platforms/php/guides/laravel/configuration/filtering/
     */
    public static function beforeSend(Event $event, ?EventHint $hint = null): ?Event
    {
        // Nettoyer les données de la requête
        if ($request = $event->getRequest()) {
            if ($data = $request['data'] ?? null) {
                $request['data'] = self::sanitizeData($data);
                $event->setRequest($request);
            }
        }

        // Nettoyer les contextes
        foreach ($event->getContexts() as $contextName => $context) {
            $event->setContext($contextName, self::sanitizeData($context));
        }

        // Nettoyer les données extra
        if ($extra = $event->getExtra()) {
            $event->setExtra(self::sanitizeData($extra));
        }

        return $event;
    }

    /**
     * Fonction récursive pour nettoyer les données
     */
    private static function sanitizeData(mixed $data): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            $lowerKey = strtolower((string) $key);

            foreach (self::SENSITIVE_KEYS as $sensitiveKey) {
                if (str_contains($lowerKey, $sensitiveKey)) {
                    $data[$key] = '[Filtered]';
                    continue 2;
                }
            }

            if (is_array($value)) {
                $data[$key] = self::sanitizeData($value);
            }
        }

        return $data;
    }
}

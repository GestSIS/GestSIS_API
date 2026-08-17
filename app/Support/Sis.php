<?php

namespace App\Support;

use Illuminate\Support\Facades\Config;

final class Sis
{
    private const CONNECTION_PREFIX = 'db_';

    public static function connection(string $db): string
    {
        return self::CONNECTION_PREFIX . $db;
    }

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return config('database.dbs');
    }

    public static function isValid(string $sisKey): bool
    {
        return in_array($sisKey, self::keys(), true);
    }

    public static function use(string $db): void
    {
        Config::set('database.default', self::connection($db));
    }

    /**
     * Exécute $callback pour chaque SIS de $keys (ou tous les SIS par défaut),
     * avec la connexion par défaut basculée sur le SIS courant. Restaure la
     * connexion par défaut d'origine une fois l'itération terminée.
     *
     * @param callable(string $db): mixed $callback
     * @param array<int, string>|null $keys
     * @return array<string, mixed>
     */
    public static function each(callable $callback, ?array $keys = null): array
    {
        $original = config('database.default');
        $results = [];

        try {
            foreach ($keys ?? self::keys() as $db) {
                self::use($db);
                $results[$db] = $callback($db);
            }
        } finally {
            Config::set('database.default', $original);
        }

        return $results;
    }
}

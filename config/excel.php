<?php

use App\Collections\SafeValueBinder;

/*
 * Surcharge partielle de la config maatwebsite/excel (fusionnée avec ses défauts).
 */
return [
    'value_binder' => [
        // Empêche toute valeur saisie ("=...") d'être écrite comme formule dans les exports.
        'default' => SafeValueBinder::class,
    ],
];

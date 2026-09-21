<?php

namespace App\Domaine\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;

/**
 * Comme ArrayException, mais jamais envoyée à Bugsink/Sentry : pour les erreurs
 * client attendues (ressource introuvable, accès refusé, jeton invalide, ...) qui ne
 * sont pas des bugs applicatifs. Rendue via le même handler que ArrayException
 * (bootstrap/app.php), puisqu'elle en hérite.
 */
class NotReportableException extends ArrayException implements ShouldntReport
{
}

<?php

namespace App\Collections;

use Maatwebsite\Excel\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

/**
 * Value binder par défaut des exports xlsx (cf. config/excel.php).
 *
 * Les valeurs exportées proviennent de saisies utilisateur, y compris du
 * formulaire public de recrutement : une chaîne commençant par "=" doit rester
 * une chaîne et ne jamais devenir une formule exécutée à l'ouverture du fichier.
 */
class SafeValueBinder extends DefaultValueBinder
{
    public static function dataTypeForValue(mixed $value): string
    {
        $type = parent::dataTypeForValue($value);

        return $type === DataType::TYPE_FORMULA ? DataType::TYPE_STRING : $type;
    }
}

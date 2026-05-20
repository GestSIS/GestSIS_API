<?php

namespace App\Domaine\Business;

class AlarmesBusiness
{
    public static function resoudreAlarmes($alarmes, $sapeurs): array
    {
        $indexedSapeursByNomPrenom = [];
        $indexedSapeursByPhone = [];
        foreach ($sapeurs as $sapeur) {
            $key = strtolower("{$sapeur->nom} {$sapeur->prenom}");
            $indexedSapeursByNomPrenom[$key] = $sapeur;
            foreach ($sapeur->telephones as $telephone) {
                $numero = self::normaliserTelephone($telephone->numero);

                // Edge case lorsqu'un unique numéro est partagé par plusieurs sapeurs
                if (array_key_exists($numero, $indexedSapeursByPhone) && $indexedSapeursByPhone[$numero] !== $sapeur->id) {
                    $indexedSapeursByPhone[$numero] = null;
                } else {
                    $indexedSapeursByPhone[$numero] = $sapeur->id;
                }
            }
        }

        return collect($alarmes)->map(function ($alarme) use ($indexedSapeursByPhone, $indexedSapeursByNomPrenom) {
            $tmp = [];
            $missing = [];

            foreach ($alarme->firefighters as $f) {
                // Method 1 en utilisant les numéros de téléphones
                $phones = explode(",", $f->phone);
                $resolved = false;
                foreach ($phones as $phone) {
                    $numero = self::normaliserTelephone($phone);
                    if ($numero !== "" && array_key_exists($numero, $indexedSapeursByPhone) && $indexedSapeursByPhone[$numero] !== null) {
                        $f->id = $indexedSapeursByPhone[$numero];
                        $tmp[] = $f;
                        $resolved = true;
                        break;
                    }
                }

                if ($resolved) {
                    continue;
                }

                // Method 2 en utilisant le nom & prénom
                $nomPrenom = strtolower($f->fullname);
                if (array_key_exists($nomPrenom, $indexedSapeursByNomPrenom)) {
                    $f->id = $indexedSapeursByNomPrenom[$nomPrenom]->id;
                    $tmp[] = $f;
                } else {
                    $missing[] = $f;
                }
            }

            $alarme->firefighters = $tmp;
            $alarme->unresolved = [];

            if ($missing !== []) {
                $alarme->unresolved = $missing;
            }

            return $alarme;
        })->all();
    }

    private static function normaliserTelephone(string $phone): string
    {
        $tmp = str_replace(" ", "", trim($phone));
        if ($tmp !== "" && str_starts_with($tmp, "0")) {
            return "+41" . substr($tmp, 1);
        }
        return $tmp;
    }
}

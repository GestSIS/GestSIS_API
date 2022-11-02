<?php

namespace App\Domaine\Business;

use App\Infrastructure\Models\MaterielGenerique;
use App\Infrastructure\Models\MaterielPersonnel;

class MatPersoBusiness
{
    public function attribuer($attributions)
    {
        // Fetch matériel à attribuer
        $ids = array_map(fn ($a) => $a['id'], $attributions);
        $mats = MaterielPersonnel::with('materiel')->whereIn('id', $ids)->get();

        $materielReference = [];
        foreach ($mats as $mat) {
            $materielReference[$mat->id] = $mat;
        }

        // Itérer sur $attributions
        foreach ($attributions as $attribution) {
            // Load matériel type

            // TODO Check si matériel numéroté
            if ($attribution->quantite === null) {
                // Update matériel existant
                MaterielPersonnel::where('id', '=', $attribution['id'])
                    ->update([
                        'retour' => null,
                        'attribution' => $attribution['date'],
                        'sapeur_id' => $attribution['sapeurId']
                    ]);
            } else {
                $materielReference = MaterielPersonnel::with('materiel')->find($attribution['id']);
                $quantiteRestante = max($materielReference->materiel->quantite - $attribution['quantite'], 0);

                // Ajout du matériel au sapeur
                $newGenerique = new MaterielGenerique();
                $newGenerique->update(['quantite' => $attribution['quantite']]);
                $newGenerique->save();

                $newMateriel = new MaterielPersonnel();
                $newMateriel->update([
                    'attribution' => $attribution['date'],
                    'retour' => null,
                    'remarque' => '',
                    'sapeur_id' => $attribution['sapeurId'],
                    'taille' => $materielReference->taille,
                    'materiel_type_id' => $materielReference->materiel_type_id,
                ]);
                $newMateriel->materiel_id = $newGenerique->id;
                $newMateriel->materiel_type = MaterielGenerique::class;
                $newMateriel->save();

                // Adapter la quantité restante dans l'inventaire
                MaterielGenerique::where('id', $materielReference->materiel->id)
                    ->update([
                        'quantite' => $quantiteRestante,
                    ]);
            }
        }
    }

    public function retour($date, $materielIds)
    {
        MaterielPersonnel::whereIn($materielIds)->update(['retour' => $date]);

        // Fetch matériel générique à remettre en stock
        $materiels = MaterielPersonnel::with('materiel')->whereIn($materielIds)->where('materiel_type', '=', MaterielGenerique::class)->get();

        // Iterate sur le matériel
        foreach ($materiels as $materiel) {
            // Mettre à jour l'inventaire
            $mat = MaterielPersonnel::with('materiel')
                ->where('materiel_type_id', '=', $materiel->materiel_type_id)
                ->where('taille', '=', $materiel->taille)
                ->where('sapeur_id', '=', null)
                ->first();
            if ($mat != null) {
                MaterielGenerique::where('id', '=', $mat->materiel->id)->update(['quantite' => $mat->materiel->quantite + $materiel->materiel->quantite]);
            } else {
                // Ajout du matériel au sapeur
                $newGenerique = new MaterielGenerique();
                $newGenerique->update(['quantite' => $materiel->materiel->quantite]);
                $newGenerique->save();

                $newMateriel = new MaterielPersonnel();
                $newMateriel->update([
                    'taille' => $materiel->taille,
                    'materiel_type_id' => $materiel->materiel_type_id,
                    'remarque' => '',
                    'sapeur_id' => null,
                    'attribution' => null,
                    'retour' => null,
                ]);
                $newMateriel->materiel_id = $newGenerique->id;
                $newMateriel->materiel_type = MaterielGenerique::class;
                $newMateriel->save();
            }
        }
    }

    public function evenement()
    {
        // TODO: Ajouter l'événement et générer les potentielles alertes
    }
}

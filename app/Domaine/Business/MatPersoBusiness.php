<?php

namespace App\Domaine\Business;

use App\Infrastructure\Models\MaterielGenerique;
use App\Infrastructure\Models\MaterielNominal;
use App\Infrastructure\Models\MaterielPersonnel;

class MatPersoBusiness
{
    public function create($materiels)
    {
        $base = [];

        foreach ($materiels as $materiel) {
            if ($materiel['materiel']['quantite'] != null) {
                $generique = new MaterielGenerique();
                $generique->numero = $materiel['numero'];
                $generique->uuid = uniqid($materiel['materiel_type_id'] . "-");
                $generique->save();

                array_push($base, [
                    'materiel_type_id' => $materiel['materiel_type_id'],
                    'materiel_type' => MaterielGenerique::class,
                    'materiel_id' => $generique->id,
                    'taille' => $materiel['taille'] ?? '',
                    'remarque' => $materiel['remarque'] ?? '',
                    'sapeur_id' => null,
                    'attribution' => null,
                    'retour' => null,
                ]);
            } else {
                $nominal = new MaterielNominal();
                $nominal->quantite = $materiel['quantite'];
                $nominal->save();

                array_push($base, [
                    'materiel_type_id' => $materiel['materiel_type_id'],
                    'materiel_type' => MaterielNominal::class,
                    'materiel_id' => $nominal->id,
                    'taille' => $materiel['taille'] ?? '',
                    'remarque' => $materiel['remarque'] ?? '',
                    'sapeur_id' => null,
                    'attribution' => null,
                    'retour' => null,
                ]);
            }
        }

        MaterielPersonnel::insert($base);
    }

    public function update($materiels)
    {
        $materielIds = [];
        $indexedMateriel = [];
        foreach ($materiels as $materiel) {
            array_push($materielIds, $materiel['id']);
            $indexedMateriel[$materiel['id']] = $materiel;
        }

        $references = MaterielPersonnel::whereIn('id', $materielIds)->get();
        foreach ($references as $reference) {
            $materiel = $indexedMateriel[$reference->id];
            MaterielPersonnel::where('id', $reference['id'])->update([
                'taille' => $materiel['taille'] ?? '',
                'remarque' => $materiel['remarque'] ?? '',
            ]);
            if ($reference->materiel_type === MaterielGenerique::class) {
                MaterielGenerique::where('id', $reference->materiel_id)->update([
                    'quantite' => $materiel['materiel']['quantite'] ?? '',
                ]);
            } else {
                MaterielNominal::where('id', $reference->materiel_id)->update([
                    'numero' => $materiel['materiel']['numero'] ?? '',
                    'achat' => $materiel['materiel']['achat'] ?? '',
                ]);
            }
        }
    }

    public function delete($materielIds)
    {
        $materiels = MaterielPersonnel::whereIn('id', $materielIds)->get();
        $generiqueIds = [];
        $nominalIds = [];
        foreach ($materiels as $materiel) {
            if ($materiel->materiel_type === MaterielGenerique::class) {
                array_push($generiqueIds, $materiel->materiel_id);
            } else {
                array_push($nominalIds, $materiel->materiel_id);
            }
        }

        MaterielPersonnel::whereIn('id', $materielIds)->delete();
        MaterielNominal::whereIn('id', $nominalIds)->delete();
        MaterielGenerique::whereIn('id', $generiqueIds)->delete();
    }

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
            if ($attribution['quantite'] === null) {
                // Update matériel existant
                MaterielPersonnel::where('id', '=', $attribution['id'])
                    ->update([
                        'retour' => null,
                        'attribution' => $attribution['date'],
                        'sapeur_id' => $attribution['sapeur_id']
                    ]);
            } else {
                $materielReference = MaterielPersonnel::with('materiel')->find($attribution['id']);
                $quantiteRestante = max($materielReference->materiel->quantite - $attribution['quantite'], 0);

                // Ajout du matériel au sapeur
                $newGenerique = new MaterielGenerique();
                $newGenerique->fill(['quantite' => $attribution['quantite']]);
                $newGenerique->save();

                $newMateriel = new MaterielPersonnel();
                $newMateriel->fill([
                    'attribution' => $attribution['date'],
                    'retour' => null,
                    'remarque' => '',
                    'sapeur_id' => $attribution['sapeur_id'],
                    'taille' => $materielReference->taille,
                ]);
                $newMateriel->materiel_type_id = $materielReference->materiel_type_id;
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
        MaterielPersonnel::whereIn('id', $materielIds)->update(['retour' => $date]);

        // Fetch matériel générique à remettre en stock
        $materiels = MaterielPersonnel::with('materiel')->whereIn('id', $materielIds)->where('materiel_type', '=', MaterielGenerique::class)->get();

        // Iterate sur le matériel
        foreach ($materiels as $materiel) {
            // Mettre à jour l'inventaire
            $mat = MaterielPersonnel::with('materiel')
                ->where('materiel_type_id', '=', $materiel->materiel_type_id)
                ->where('taille', '=', $materiel->taille)
                ->where('sapeur_id', '=', null)
                ->where('materiel_type', '=', MaterielGenerique::class)
                ->first();

            if ($mat != null) {
                MaterielGenerique::where('id', '=', $mat->materiel->id)->update(['quantite' => $mat->materiel->quantite + $materiel->materiel->quantite]);
            } else {
                // Ajout du matériel au sapeur
                $newGenerique = new MaterielGenerique();
                $newGenerique->fill(['quantite' => $materiel->materiel->quantite]);
                $newGenerique->save();

                $newMateriel = new MaterielPersonnel();
                $newMateriel->fill([
                    'taille' => $materiel->taille,
                    'remarque' => '',
                    'sapeur_id' => null,
                    'attribution' => null,
                    'retour' => null,
                ]);
                $newMateriel->materiel_type_id = $materiel->materiel_type_id;
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

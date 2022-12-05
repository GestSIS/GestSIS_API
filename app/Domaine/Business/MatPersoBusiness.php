<?php

namespace App\Domaine\Business;

use App\Infrastructure\Models\MaterielAlerte;
use App\Infrastructure\Models\MaterielAlerteType;
use App\Infrastructure\Models\MaterielEvent;
use App\Infrastructure\Models\MaterielEventType;
use App\Infrastructure\Models\MaterielGenerique;
use App\Infrastructure\Models\MaterielNominal;
use App\Infrastructure\Models\MaterielPersonnel;

class MatPersoBusiness
{
    public const ALERTE_STATUT_OK = 1;
    public const ALERTE_STATUT_DESACTIVE = 0;

    public function createEvents($events)
    {
        $alertes = MaterielAlerteType::with('eventTypes')->get();

        foreach ($events as $event) {
            $materiel = MaterielNominal::find($event['materiel_nominal_id']);
            if ($materiel == null) {
                continue;
            }

            $materielEventType = MaterielEventType::with('alerteTypes')->find($event['materiel_event_type_id']);
            if ($materielEventType == null) {
                continue;
            }

            $e = new MaterielEvent();
            $e->fill($event);
            $e->materiel_nominal_id = $event['materiel_nominal_id'];
            $e->remarque = $event['remarque'] ?? '';
            $e->materiel_event_type_id = $event['materiel_event_type_id'];
            $e->succes = $event['succes'] ?? false;
            $e->save();

            $alertes = $materielEventType->alerteTypes()->get();
            foreach ($alertes as $alerte) {
                // ['titre', 'description', 'seuil_min', 'dernier']
                $generateAlerte = false;
                if ($alerte->dernier) {
                    $generateAlerte = !$materiel->events()->where('materiel_event_type_id', '=', $e->materiel_event_type_id)->orderBy('date', 'desc')->first()->succes;
                } else {
                    $generateAlerte = $materiel->events()->where('materiel_event_type_id', '=', $e->materiel_event_type_id)->count() >= $alerte->seuil_min;
                }

                if ($generateAlerte == true || $generateAlerte == 1) {
                    $a = new MaterielAlerte();
                    // ['titre', 'description', 'materiel_nominal_id', 'statut', 'remarque']
                    $a->titre = $alerte->titre;
                    $a->description = $alerte->description;
                    $a->materiel_nominal_id = $e->materiel_nominal_id;
                    $a->statut = self::ALERTE_STATUT_OK;
                    $a->remarque = '';
                    $a->save();
                }
            }
        }
    }

    public function create($materiels)
    {
        $base = [];

        foreach ($materiels as $materiel) {
            if ($materiel['materiel']['quantite'] ?? null != null) {
                $generique = new MaterielGenerique();
                $generique->quantite = $materiel['materiel']['quantite'];
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
                $nominal->numero = $materiel['materiel']['numero'];
                $nominal->achat = $materiel['materiel']['achat'] ?? '';
                $nominal->uuid = uniqid($materiel['materiel_type_id'] . "-");
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
        // // Fetch matériel à attribuer
        // $ids = array_map(fn ($a) => $a['id'], $attributions);
        // $mats = MaterielPersonnel::with('materiel')->whereIn('id', $ids)->get();

        // $materielReference = [];
        // foreach ($mats as $mat) {
        //     $materielReference[$mat->id] = $mat;
        // }

        // Itérer sur $attributions
        foreach ($attributions as $attribution) {
            // Check si matériel numéroté
            if ($attribution['quantite'] === null) {
                if ($attribution['id'] ?? null) {
                    // Update matériel existant
                    MaterielPersonnel::where('id', '=', $attribution['id'])
                        ->update([
                            'retour' => null,
                            'attribution' => $attribution['date'],
                            'sapeur_id' => $attribution['sapeur_id'],
                            'remarque' => $attribution['remarque'] ?? ''
                        ]);
                } else {
                    // TODO: Créer le nouveau matériel
                    $nominal = new MaterielNominal();
                    $nominal->numero = $attribution['numero'];
                    $nominal->achat = $attribution['achat'] ?? '';
                    $nominal->uuid = uniqid($attribution['materiel_type_id'] . "-");
                    $nominal->save();

                    MaterielPersonnel::insert([
                        'materiel_type_id' => $attribution['materiel_type_id'],
                        'materiel_type' => MaterielNominal::class,
                        'materiel_id' => $nominal->id,
                        'taille' => $attribution['taille'] ?? '',
                        'remarque' => $attribution['remarque'] ?? '',
                        'sapeur_id' => $attribution['sapeur_id'],
                        'attribution' => $attribution['date'],
                        'retour' => null,
                    ]);
                }
            } else {
                // Matériel générique
                $materielReference = null;
                if ($attribution['id'] ?? null != null) {
                    $materielReference = MaterielPersonnel::with('materiel')->find($attribution['id']);
                } else {
                    $materielReference = MaterielPersonnel::with('materiel')->where([
                        ['sapeur_id', '=', null],
                        ['taille', '=', $attribution['taille'] ?? ''],
                        ['materiel_type_id', '=', $attribution['materiel_type_id']],
                    ])->first();
                }

                // Ajout du matériel au sapeur
                $newGenerique = new MaterielGenerique();
                $newGenerique->fill(['quantite' => $attribution['quantite']]);
                $newGenerique->save();

                $newMateriel = new MaterielPersonnel();
                $newMateriel->fill([
                    'attribution' => $attribution['date'],
                    'retour' => null,
                    'remarque' => $attribution['remarque'] ?? '',
                    'sapeur_id' => $attribution['sapeur_id'],
                    'taille' => $materielReference->taille ?? $attribution['taille'] ?? '',
                ]);
                $newMateriel->materiel_type_id = $materielReference->materiel_type_id ?? $attribution['materiel_type_id'];
                $newMateriel->materiel_id = $newGenerique->id;
                $newMateriel->materiel_type = MaterielGenerique::class;
                $newMateriel->save();

                if ($materielReference) {
                    $quantiteRestante = max($materielReference->materiel->quantite - $attribution['quantite'], 0);
                    // Adapter la quantité restante dans l'inventaire
                    MaterielGenerique::where('id', $materielReference->materiel->id)
                        ->update([
                            'quantite' => $quantiteRestante,
                        ]);
                }
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

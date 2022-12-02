<?php

namespace App\Domaine\API;

use App\Domaine\Business\MatPersoBusiness;
use App\Infrastructure\Models\MaterielAlerte;
use App\Infrastructure\Models\MaterielNominal;
use App\Infrastructure\Models\MaterielPersonnel;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MatPersoService
{
    protected $business;

    public function __construct(MatPersoBusiness $business)
    {
        $this->business = $business;
    }

    public function materiels()
    {
        return MaterielPersonnel //::with('materiel')->get();
            ::with([
                'materiel' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        MaterielNominal::class => ['events'],
                    ]);
                }
            ])->get();
    }

    public function aRecuperer()
    {
        return MaterielPersonnel::where('retour', '=', null)->whereHas('sapeur', fn ($q) => $q->where('actif', '=', false))->with('materiel')->get();
    }

    public function alertes()
    {
        return MaterielAlerte::with('materielNominal.materiel')->get();
    }

    public function createEvents($events)
    {
        $this->business->createEvents($events);
        return $this->materiels();
    }

    public function create($materiels)
    {
        $this->business->create($materiels);
        return $this->materiels();
    }

    public function update($materiels)
    {
        $this->business->update($materiels);
        return $this->materiels();
    }

    public function delete($materiels)
    {
        return $this->business->delete($materiels);
    }

    public function attribuer($materiels)
    {
        $this->business->attribuer($materiels);
        return $this->materiels();
    }

    public function retour($date, $materielIds)
    {
        $this->business->retour($date, $materielIds);
        return $this->materiels();
    }

    // public function modifierCategorie($id, $data)
    // {
    //     return $this->business->modifierCategorie($id, $data);
    // }

    // public function supprimerCategorie($id)
    // {
    //     return $this->business->supprimerCategorie($id);
    // }
}

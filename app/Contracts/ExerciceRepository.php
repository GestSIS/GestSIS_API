<?php


namespace App\Contracts;


interface ExerciceRepository extends Repository //TODO Remove extends
{
    public function findWithSapeurs($exercice_id);
}

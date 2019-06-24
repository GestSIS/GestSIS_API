<?php


namespace App\Contracts;


interface ExerciceRepository extends Repository
{
    public function findWithSapeurs($exercice_id);
}

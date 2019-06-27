<?php


namespace App\Contracts;


interface IndemniteTypeRepository
{
    public function listeIndemniteExerciceType();
    public function listeIndemniteInterventionType();

    public function findIndemniteExerciceTypeById(int $id);
    public function findIndemniteInterventionTypeById(int $id);
}

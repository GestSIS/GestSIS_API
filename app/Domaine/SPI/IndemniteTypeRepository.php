<?php


namespace App\Domaine\SPI;


interface IndemniteTypeRepository
{
    public function listeIndemniteExerciceType();
    public function listeIndemniteInterventionType();
    public function listeFraisIndemniteAnnuelType();

    public function findIndemniteExerciceTypeById(int $id);
    public function findIndemniteInterventionTypeById(int $id);
}

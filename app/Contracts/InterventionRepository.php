<?php


namespace App\Contracts;


interface InterventionRepository extends Repository
{
    public function findWith($intervention_id, $with = []);
}

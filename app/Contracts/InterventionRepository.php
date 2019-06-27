<?php


namespace App\Contracts;


interface InterventionRepository extends Repository //TODO Remove extends
{
    public function findWith($intervention_id, $with = []);
}

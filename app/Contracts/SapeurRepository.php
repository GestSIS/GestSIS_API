<?php


namespace App\Contracts;


interface SapeurRepository
{
    public function listeSapeurLight();

    public function getSapeurDetailsById($id, $with = []);
}

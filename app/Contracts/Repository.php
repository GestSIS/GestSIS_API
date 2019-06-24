<?php


namespace App\Contracts;


interface Repository {

    public function all($columns = array('*'));

    public function create(array $data);

    public function update(array $data, $id);

    public function delete($id);

    public function find($id, $columns = array('*'));
}

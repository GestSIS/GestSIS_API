<?php


namespace App\Contracts;

//TODO Remove this class
interface Repository {

    public function all($columns = array('*'));

    public function create(array $data);

    public function update(array $data, $id);

    public function delete($id);

    public function find($id, $columns = array('*'));
}

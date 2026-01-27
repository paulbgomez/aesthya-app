<?php

namespace App\Repositories;

/**
 * RepositoryInterface provides the standard functions to be expected of ANY
 * repository.
 */

interface RepositoryInterface
{
    public function all($columns = array('*'));

    public function find($id, $columns = array('*'));

    public function findBy($field, $value, $columns = array('*'));

}

<?php

namespace App\Repositories;

class Repository
{
    protected const CACHE_TIMEOUT = 60 * 60 * 24;
    protected $baseModel = null;
    protected bool $cacheReset = false;

    public function __construct()
    {
    }

    protected function processToModelMulti($results, $model = null): ?array
    {
        if (!empty($results)) {
            $model = !empty($model) ? $model : $this->baseModel;
            $resultArray = array();
            foreach ($results as $row) {
                if (gettype($row) === 'object') {
                    $row = (array) $row;
                }
                $resultArray[] = !empty($model) ? new $model($row) : $row;
            }

            return $resultArray;
        } else {
            return null;
        }
    }

    protected function processToModel($results, $model = null)
    {
        if (!empty($results)) {
            $model = !empty($model) ? $model : $this->baseModel;
            if (isset($results[0])) {
                $row = (array) $results[0];
            } else {
                $row = (array) $results;
            }

            return !empty($model) ? new $model($row) : $row;
        } else {
            return null;
        }
    }
}

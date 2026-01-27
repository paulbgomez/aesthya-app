<?php

namespace App\Repositories\Feelings;

use App\Repositories\Repository;
use Illuminate\Support\Facades\DB;

class FeelingsRepository extends Repository
{
    protected $baseModel = 'App\Models\Feeling';

    public function getAllFeelings()
    {
        $results = DB::table('feelings')->get();
        return $this->processToModelMulti($results);
    }
}
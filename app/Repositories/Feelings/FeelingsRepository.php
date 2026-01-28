<?php

namespace App\Repositories\Feelings;

use App\Repositories\Repository;
use Illuminate\Support\Facades\DB;

class FeelingsRepository extends Repository
{
    protected $baseModel = 'App\Models\Feeling';

    /**
     * Get all feelings.
     */
    public function getAllFeelings()
    {
        $results = DB::table('feelings')->get();
        return $this->processToModelMulti($results);
    }

    /**
     * Get a feeling by its ID.
     */
    public function getFeelingById(int $id)
    {
        $result = DB::table('feelings')->where('id', $id)->first();
        
        if (!$result) {
            return null;
        }

        return $this->processToModel((array) $result);
    }
}
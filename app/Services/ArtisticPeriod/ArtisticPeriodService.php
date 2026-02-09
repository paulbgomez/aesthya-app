<?php

namespace App\Services\ArtisticPeriod;

use App\Models\ArtisticPeriod;

class ArtisticPeriodService
{
    public function __construct(
    ) {}

    public function processArtisticPeriodData(array $artisticPeriod): ?int
    {
        $existingArtisticPeriod = ArtisticPeriod::where('name', $artisticPeriod['name'])->first();

        if ($existingArtisticPeriod) {
            return $existingArtisticPeriod->id;
        }

        $artisticPeriodData = [
            'name' => $artisticPeriod['name'],
            'explanation' => $artisticPeriod['explanation'] ?? null,
            'years' => $artisticPeriod['years'] ?? null,
        ];

        $createArtisticPeriod = ArtisticPeriod::create($artisticPeriodData);

        return $createArtisticPeriod->id;
    }
}

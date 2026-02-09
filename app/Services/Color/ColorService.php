<?php

namespace App\Services\Color;

use App\Models\Color;

class ColorService
{
    public function __construct(
    ) {}

    public function processColorData(array $color): int|null
    {
        $existingColor = Color::where('hex', $color['hex'])->first();
        
        if ($existingColor) {
            return $existingColor->id;
        }

        $colorData = [
            'name' => $color['name'],
            'hex' => $color['hex'] ?? null,
            'explanation' => $color['explanation'] ?? null,
            'pantone' => $color['pantone'] ?? null,
        ];
        
        $color = Color::create($colorData);

        return $color->id;
    }
}
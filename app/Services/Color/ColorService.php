<?php

namespace App\Services\Color;

use App\Models\Color;

class ColorService
{
    public function __construct(
    ) {}

    public function processColorData(Color $color): int|null
    {
        $existingColor = Color::where('name', $color->name)->first();
        
        if ($existingColor) {
            return $existingColor->id;
        }

        $colorData = [
            'name' => $color->name,
            'hex_code' => $color->hex_code ?? null,
            'explanation' => $color->explanation ?? null,
            'pantone' => $color->pantone ?? null,
        ];
        
        $color = Color::create($colorData);

        return $color->id;
    }
}
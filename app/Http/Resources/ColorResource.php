<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ColorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hexCode' => $this->hex,
            'name' => $this->name,
            'explanation' => $this->explanation,
        ];
    }
}

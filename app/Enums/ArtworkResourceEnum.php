<?php

namespace App\Enums;

enum ArtworkResourceEnum: string
{
    case LLM = 'LLM';
    case Wikidata = 'Wikidata';
    case Wikipedia = 'Wikipedia';
}
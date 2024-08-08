<?php

namespace App\Enum;

enum SuperMarketGlobals: string
{
    case ITEM_TYPE_FRUIT = 'fruit';
    case ITEM_TYPE_VEGETABLE = 'vegetable';
    case WEIGHT_GRAMS = 'g'; // grams
    case WEIGHT_KILOGRAMS = 'kg'; // kilograms
}
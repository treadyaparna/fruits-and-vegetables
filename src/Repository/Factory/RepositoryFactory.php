<?php

namespace App\Repository\Factory;

use App\Enum\SuperMarketGlobals;
use App\Exception\InvalidItemTypeException;
use App\Repository\FruitRepository;
use App\Repository\VegetableRepository;

class RepositoryFactory
{
    public function __construct(
        private FruitRepository     $fruitRepo,
        private VegetableRepository $vegetableRepo
    )
    {
        //
    }

    /**
     * Get the repository based on an item type
     *
     * @param $type
     * @return mixed
     * @throws InvalidItemTypeException
     */
    public function getRepository($type)
    {
        return match ($type) {
            SuperMarketGlobals::ITEM_TYPE_FRUIT->value => $this->fruitRepo,
            SuperMarketGlobals::ITEM_TYPE_VEGETABLE->value => $this->vegetableRepo,
            default => throw new InvalidItemTypeException(),
        };
    }
}
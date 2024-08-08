<?php

namespace App\Service;

use App\Entity\Fruit;
use App\Entity\Vegetable;
use App\Enum\SuperMarketGlobals;
use App\Exception\InvalidItemException;
use App\Exception\InvalidItemTypeException;
use App\Exception\NoItemException;
use App\Helper\UnitConverter;
use App\Repository\Factory\RepositoryFactory;
use Exception;

class StorageService
{
    public function __construct(
        private RepositoryFactory    $repositoryFactory,
        private ElasticsearchService $elasticsearchService,
        private UnitConverter        $unitConverter

    )
    {
        //
    }

    /**
     * Add item to the respective collections
     *
     * @param $item
     * @return void
     * @throws InvalidItemException
     * @throws InvalidItemTypeException
     */
    public function add($item)
    {
        // check for an item type
        if (!isset($item['type'])) {
            throw new InvalidItemException();
        }

        // convert item quantity to grams
        $item['quantity'] = $item['unit'] === SuperMarketGlobals::WEIGHT_KILOGRAMS->value
            ? $this->unitConverter->convertToGrams($item['quantity'])
            : $item['quantity'];

        // fetch relevant repo
        $repo = $this->repositoryFactory->getRepository($item['type']);

        try {
            if ($repo->add($item)) {
                unset($item['id']);
                $this->elasticsearchService->add($item);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Remove item from the respective collections
     *
     * @param $id
     * @param $type
     * @return void
     * @throws InvalidItemException
     * @throws InvalidItemTypeException
     * @throws NoItemException
     */
    public function remove($id, $type)
    {
        // check for an item type
        if (!isset($type)) {
            throw new InvalidItemException();
        }

        // fetch relevant repo
        $repo = $this->repositoryFactory->getRepository($type);

        $existingItem = $repo->find($id);

        if (!empty($existingItem)) {
            if ($repo->delete($existingItem)) {
                $this->elasticsearchService->remove($type, $existingItem->getName());
            }
        } else {
            throw new NoItemException();
        }
    }

    /**
     * @param $type
     * @param $unit
     * @param $name
     * @return Fruit[] | Vegetable[]
     * @throws InvalidItemTypeException
     */
    public function list($type, $unit = SuperMarketGlobals::WEIGHT_GRAMS->value, $name = '')
    {
        $repo = $this->repositoryFactory->getRepository($type);

        if ($name !== '') {
            $items = $repo->findByName($name);
        } else {
            $items = $repo->findAll();
        }

        $itemsInUnit = $this->processItems($items, $unit);

        return $itemsInUnit;
    }

    /**
     * convert entity to array
     *
     * @param $itemEntities
     * @param $unit
     * @return array
     */
    private function processItems($itemEntities, $unit)
    {
        $items = [];
        foreach ($itemEntities as $item) {
            $items[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'quantity' =>
                    ($unit === SuperMarketGlobals::WEIGHT_KILOGRAMS->value)
                        ? $this->unitConverter->convertToKilograms($item->getQuantity())
                        : $item->getQuantity(),
            ];
        }

        return $items;
    }

    /**
     * Search by name in Elasticsearch.
     *
     * @param string $name The name to search for.
     * @return array The search results.
     */
    public function searchByName(string $name)
    {
        return $this->elasticsearchService->searchByName($name);
    }
}

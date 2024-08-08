<?php

namespace App\Tests\App\Service;

use App\Entity\Fruit;
use App\Entity\Vegetable;
use App\Enum\SuperMarketGlobals;
use App\Exception\InvalidItemException;
use App\Exception\NoItemException;
use App\Helper\UnitConverter;
use App\Repository\Factory\RepositoryFactory;
use App\Repository\FruitRepository;
use App\Repository\VegetableRepository;
use App\Service\ElasticsearchService;
use App\Service\StorageService;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StorageServiceTest extends TestCase
{
    /** @var RepositoryFactory|MockObject */
    private $repositoryFactory;

    /** @var ElasticsearchService|MockObject */
    private $elasticsearchService;

    /** @var UnitConverter|MockObject */
    private $unitConverter;

    /** @var StorageService */
    private $storageService;

    /** @var FruitRepository|MockObject */
    private $fruitRepo;

    /** @var VegetableRepository|MockObject */
    private $vegetableRepo;

    protected function setUp(): void
    {
        $this->vegetableRepo = $this->createMock(VegetableRepository::class);
        $this->fruitRepo = $this->createMock(FruitRepository::class);
        $this->repositoryFactory = $this->createMock(RepositoryFactory::class);
        $this->elasticsearchService = $this->createMock(ElasticsearchService::class);
        $this->unitConverter = $this->createMock(UnitConverter::class);

        $this->storageService = new StorageService(
            $this->repositoryFactory,
            $this->elasticsearchService,
            $this->unitConverter
        );
    }

    protected function tearDown(): void
    {
        $this->repositoryFactory = null;
        $this->elasticsearchService = null;
        $this->unitConverter = null;
        $this->storageService = null;
        $this->fruitRepo = null;
        $this->vegetableRepo = null;
    }

    public function testListWithNameFilter()
    {
        $this->repositoryFactory
            ->expects($this->once())
            ->method('getRepository')
            ->with('Fruit')
            ->willReturn($this->fruitRepo);

        $fruit = new Fruit();
        $fruit->setId(1);
        $fruit->setName('Apple');
        $fruit->setQuantity(1000);

        $this->fruitRepo
            ->expects($this->once())
            ->method('findByName')
            ->with('Apple')
            ->willReturn([$fruit]);

        $this->unitConverter
            ->expects($this->never())
            ->method('convertToKilograms');

        $result = $this->storageService->list('Fruit', SuperMarketGlobals::WEIGHT_GRAMS->value, 'Apple');
        $expected = [
            [
                'id' => 1,
                'name' => 'Apple',
                'quantity' => 1000,
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testListWithUnitConversion()
    {
        $this->repositoryFactory
            ->expects($this->once())
            ->method('getRepository')
            ->with('Vegetable')
            ->willReturn($this->vegetableRepo);

        $vegetable = new Vegetable();
        $vegetable->setId(2);
        $vegetable->setName('Carrot');
        $vegetable->setQuantity(1500);

        $this->vegetableRepo
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$vegetable]);

        $this->unitConverter
            ->expects($this->once())
            ->method('convertToKilograms')
            ->with(1500)
            ->willReturn(1.5);

        $result = $this->storageService->list('Vegetable', SuperMarketGlobals::WEIGHT_KILOGRAMS->value);
        $expected = [
            [
                'id' => 2,
                'name' => 'Carrot',
                'quantity' => 1.5,
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testListWithNoItems()
    {
        $this->repositoryFactory
            ->expects($this->once())
            ->method('getRepository')
            ->with('Fruit')
            ->willReturn($this->fruitRepo);

        $this->fruitRepo
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $result = $this->storageService->list('Fruit');
        $expected = [];

        $this->assertEquals($expected, $result);
    }

    public function testListWithNameFilterAndNoMatches()
    {
        $this->repositoryFactory
            ->expects($this->once())
            ->method('getRepository')
            ->with('Vegetable')
            ->willReturn($this->vegetableRepo);

        $this->vegetableRepo
            ->expects($this->once())
            ->method('findByName')
            ->with('NonExistentName')
            ->willReturn([]);

        $result = $this->storageService->list('Vegetable', SuperMarketGlobals::WEIGHT_GRAMS->value, 'NonExistentName');
        $expected = [];

        $this->assertEquals($expected, $result);
    }

    public function testListWithInvalidUnit()
    {
        $this->repositoryFactory
            ->expects($this->once())
            ->method('getRepository')
            ->with('Fruit')
            ->willReturn($this->fruitRepo);

        $fruit = new Fruit();
        $fruit->setId(4);
        $fruit->setName('Orange');
        $fruit->setQuantity(500);

        $this->fruitRepo
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$fruit]);

        $this->unitConverter
            ->expects($this->never())
            ->method('convertToKilograms');

        $result = $this->storageService->list('Fruit', 'InvalidUnit');
        $expected = [
            [
                'id' => 4,
                'name' => 'Orange',
                'quantity' => 500,
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testListForVegetablesWithDifferentUnits()
    {
        $this->repositoryFactory
            ->method('getRepository')
            ->with('Vegetable')
            ->willReturn($this->vegetableRepo);

        $vegetable = new Vegetable();
        $vegetable->setId(5);
        $vegetable->setName('Lettuce');
        $vegetable->setQuantity(2000);

        $this->vegetableRepo
            ->method('findAll')
            ->willReturn([$vegetable]);

        $this->unitConverter
            ->method('convertToKilograms')
            ->with(2000)
            ->willReturn(2.0);

        $resultGrams = $this->storageService->list('Vegetable', SuperMarketGlobals::WEIGHT_GRAMS->value);
        $expectedGrams = [[
            'id' => 5,
            'name' => 'Lettuce',
            'quantity' => 2000,
        ]];
        $this->assertEquals($expectedGrams, $resultGrams);

        $resultKilograms = $this->storageService->list('Vegetable', SuperMarketGlobals::WEIGHT_KILOGRAMS->value);
        $expectedKilograms = [[
            'id' => 5,
            'name' => 'Lettuce',
            'quantity' => 2.0,
        ]];
        $this->assertEquals($expectedKilograms, $resultKilograms);
    }

    public function testAddValidItemInGrams()
    {
        $item = [
            'type' => 'Fruit',
            'name' => 'Apple',
            'quantity' => 1000,
            'unit' => SuperMarketGlobals::WEIGHT_GRAMS->value
        ];

        $this->repositoryFactory
            ->expects($this->once())
            ->method('getRepository')
            ->with('Fruit')
            ->willReturn($this->fruitRepo);

        $this->fruitRepo
            ->expects($this->once())
            ->method('add')
            ->with($item)
            ->willReturn(true);

        $this->elasticsearchService
            ->expects($this->once())
            ->method('add')
            ->with($item);

        $this->storageService->add($item);
    }

    public function testAddItemWithoutType()
    {
        $this->expectException(InvalidItemException::class);

        $item = [
            'name' => 'Orange',
            'quantity' => 500,
            'unit' => SuperMarketGlobals::WEIGHT_GRAMS->value
        ];

        $this->storageService->add($item);
    }

    public function testAddRepositoryThrowsException()
    {
        $item = [
            'type' => 'Vegetable',
            'name' => 'Carrot',
            'quantity' => 300,
            'unit' => SuperMarketGlobals::WEIGHT_GRAMS->value
        ];

        $this->repositoryFactory
            ->expects($this->once())
            ->method('getRepository')
            ->with('Vegetable')
            ->willReturn($this->vegetableRepo);

        $this->vegetableRepo
            ->expects($this->once())
            ->method('add')
            ->with($item)
            ->willThrowException(new Exception('Database error'));

        $this->elasticsearchService
            ->expects($this->never())
            ->method('add');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Database error');

        $this->storageService->add($item);
    }

    public function testAddItemWithExceptionInElasticsearch()
    {
        $item = [
            'type' => 'Fruit',
            'name' => 'Pineapple',
            'quantity' => 1500,
            'unit' => SuperMarketGlobals::WEIGHT_GRAMS->value
        ];

        $this->repositoryFactory
            ->expects($this->once())
            ->method('getRepository')
            ->with('Fruit')
            ->willReturn($this->fruitRepo);

        $this->fruitRepo
            ->expects($this->once())
            ->method('add')
            ->with($item)
            ->willReturn(true);

        $this->elasticsearchService
            ->expects($this->once())
            ->method('add')
            ->with($item)
            ->willThrowException(new Exception('Elasticsearch error'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Elasticsearch error');

        $this->storageService->add($item);
    }

    public function testRemoveValidItem()
    {
        $id = 1;
        $type = 'Fruit';

        $item = $this->createMock(\App\Entity\Fruit::class);
        $repo = $this->createMock(\App\Repository\FruitRepository::class);

        $this->repositoryFactory
            ->expects($this->once())
            ->method('getRepository')
            ->with($type)
            ->willReturn($repo);

        $repo
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($item);

        $repo
            ->expects($this->once())
            ->method('delete')
            ->with($item)
            ->willReturn(true);

        $this->elasticsearchService
            ->expects($this->once())
            ->method('remove')
            ->with($type, $item->getName());

        $this->storageService->remove($id, $type);
    }

    public function testRemoveItemWithInvalidType()
    {
        $this->expectException(InvalidItemException::class);

        $this->storageService->remove(1, null);
    }

    public function testRemoveItemWhenRepositoryDeleteFails()
    {
        $id = 3;
        $type = 'Fruit';

        $item = $this->createMock(\App\Entity\Fruit::class);
        $repo = $this->createMock(\App\Repository\FruitRepository::class);

        $this->repositoryFactory
            ->expects($this->once())
            ->method('getRepository')
            ->with($type)
            ->willReturn($repo);

        $repo
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($item);

        $repo
            ->expects($this->once())
            ->method('delete')
            ->with($item)
            ->willReturn(false);

        $this->elasticsearchService
            ->expects($this->never())
            ->method('remove');

        $this->storageService->remove($id, $type);
    }

    public function testRemoveItemWhenElasticsearchThrowsException()
    {
        $id = 4;
        $type = 'Vegetable';

        $item = $this->createMock(Vegetable::class);

        $this->repositoryFactory
            ->expects($this->once())
            ->method('getRepository')
            ->with($type)
            ->willReturn($this->vegetableRepo);

        $this->vegetableRepo
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($item);

        $this->vegetableRepo
            ->expects($this->once())
            ->method('delete')
            ->with($item)
            ->willReturn(true);

        $this->elasticsearchService
            ->expects($this->once())
            ->method('remove')
            ->with($type, $item->getName())
            ->willThrowException(new Exception('Elasticsearch error'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Elasticsearch error');

        $this->storageService->remove($id, $type);
    }

    public function testAddWithInvalidUnit()
    {
        $item = [
            'type' => 'Fruit',
            'name' => 'Apple',
            'quantity' => 1000,
            'unit' => 'InvalidUnit'
        ];

        $this->repositoryFactory
            ->expects($this->once())
            ->method('getRepository')
            ->with('Fruit')
            ->willReturn($this->fruitRepo);

        $this->fruitRepo
            ->expects($this->once())
            ->method('add')
            ->with($item)
            ->willReturn(true);

        $this->elasticsearchService
            ->expects($this->once())
            ->method('add')
            ->with($item);

        $this->storageService->add($item);
    }

    public function testRemoveNonExistingItem()
    {
        $id = 2;
        $type = 'Vegetable';

        $this->repositoryFactory
            ->expects($this->once())
            ->method('getRepository')
            ->with($type)
            ->willReturn($this->vegetableRepo);

        $this->vegetableRepo
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn(null);

        $this->elasticsearchService
            ->expects($this->never())
            ->method('remove');

        $this->expectException(NoItemException::class);

        $this->storageService->remove($id, $type);
    }

    public function testRemoveItemThatDoesNotExist()
    {
        $id = 2;
        $type = 'Vegetable';

        $this->repositoryFactory
            ->expects($this->once())
            ->method('getRepository')
            ->with($type)
            ->willReturn($this->vegetableRepo);

        $this->vegetableRepo
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn(null);

        $this->elasticsearchService
            ->expects($this->never())
            ->method('remove');

        $this->expectException(NoItemException::class);

        $this->storageService->remove($id, $type);
    }

    public function testListWithValidTypeButNoItems()
    {
        $type = 'Fruit';
        $this->repositoryFactory
            ->expects($this->once())
            ->method('getRepository')
            ->with($type)
            ->willReturn($this->fruitRepo);

        $this->fruitRepo
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $result = $this->storageService->list($type);
        $expected = [];

        $this->assertEquals($expected, $result);
    }

}

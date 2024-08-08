<?php

namespace App\Tests\App\Factory;

use App\Enum\SuperMarketGlobals;
use App\Exception\InvalidItemTypeException;
use App\Repository\Factory\RepositoryFactory;
use App\Repository\FruitRepository;
use App\Repository\VegetableRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RepositoryFactoryTest extends TestCase
{
    /** @var RepositoryFactory|MockObject */
    private $repositoryFactory;

    /** @var FruitRepository|MockObject */
    private $fruitRepo;

    /** @var VegetableRepository|MockObject */
    private $vegetableRepo;

    protected function setUp(): void
    {
        $this->fruitRepo = $this->createMock(FruitRepository::class);
        $this->vegetableRepo = $this->createMock(VegetableRepository::class);

        $this->repositoryFactory = new RepositoryFactory($this->fruitRepo, $this->vegetableRepo);
    }

    protected function tearDown(): void
    {
        $this->repositoryFactory = null;
        $this->fruitRepo = null;
        $this->vegetableRepo = null;
    }

    public function testGetRepositoryReturnsFruitRepository()
    {
        $repository = $this->repositoryFactory->getRepository(
            SuperMarketGlobals::ITEM_TYPE_FRUIT->value
        );
        $this->assertInstanceOf(FruitRepository::class, $repository);
    }

    public function testGetRepositoryReturnsVegetableRepository()
    {
        $repository = $this->repositoryFactory->getRepository(
            SuperMarketGlobals::ITEM_TYPE_VEGETABLE->value
        );
        $this->assertInstanceOf(VegetableRepository::class, $repository);
    }

    public function testGetRepositoryThrowsExceptionForInvalidType()
    {
        $this->expectException(InvalidItemTypeException::class);
        $this->repositoryFactory->getRepository('InvalidType');
    }

    public function testGetRepositoryThrowsExceptionForEmptyType()
    {
        $this->expectException(InvalidItemTypeException::class);
        $this->repositoryFactory->getRepository('');
        $this->repositoryFactory->getRepository(null);
    }

    public function testGetRepositoryThrowsExceptionForNullType()
    {
        $this->expectException(InvalidItemTypeException::class);
        $this->repositoryFactory->getRepository('');
        $this->repositoryFactory->getRepository(null);
    }
}
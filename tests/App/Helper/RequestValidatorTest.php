<?php

namespace App\Tests\App\Helper;

use App\Enum\SuperMarketGlobals;
use App\Helper\RequestValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestValidatorTest extends TestCase
{
    /** @var ValidatorInterface|MockObject */
    private $validator;

    /** @var RequestValidator|MockObject */
    private $requestValidator;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->requestValidator = new RequestValidator($this->validator);
    }

    protected function tearDown(): void
    {
        $this->validator = null;
        $this->requestValidator = null;
    }

    public function testValidAddRequestWithValidData()
    {
        $item = [
            'type' => SuperMarketGlobals::ITEM_TYPE_FRUIT->value,
            'name' => 'Apple',
            'quantity' => 10,
            'unit' => SuperMarketGlobals::WEIGHT_GRAMS->value,
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($item, $this->isInstanceOf(Assert\Collection::class))
            ->willReturn(new ConstraintViolationList());

        $result = $this->requestValidator->validAddRequest($item);

        $this->assertEmpty($result);
    }

    public function testValidAddRequestWithInvalidData()
    {
        $item = [
            'type' => '',
            'name' => '',
            'quantity' => 'not a number',
            'unit' => 'invalid unit',
        ];

        $violations = new ConstraintViolationList([
            new ConstraintViolation('Item type can not be blank', null, [], '', 'type', ''),
            new ConstraintViolation('Item name can not be blank', null, [], '', 'name', ''),
            new ConstraintViolation('Item quantity can not be blank', null, [], '', 'quantity', ''),
            new ConstraintViolation('Invalid unit', null, [], '', 'unit', ''),
        ]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($item, $this->isInstanceOf(Assert\Collection::class))
            ->willReturn($violations);

        $result = $this->requestValidator->validAddRequest($item);

        $this->assertCount(4, $result);
        $this->assertContains('Item type can not be blank', $result);
        $this->assertContains('Item name can not be blank', $result);
        $this->assertContains('Item quantity can not be blank', $result);
        $this->assertContains('Invalid unit', $result);
    }

    public function testValidListRequestWithValidData()
    {
        $type = SuperMarketGlobals::ITEM_TYPE_FRUIT->value;
        $unit = SuperMarketGlobals::WEIGHT_GRAMS->value;
        $name = 'Apple';

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with(
                ['type' => $type, 'unit' => $unit, 'name' => $name],
                $this->isInstanceOf(Assert\Collection::class)
            )
            ->willReturn(new ConstraintViolationList());

        $result = $this->requestValidator->validListRequest($type, $unit, $name);

        $this->assertEmpty($result);
    }

    public function testValidListRequestWithInvalidData()
    {
        $type = '';
        $unit = 'invalid unit';
        $name = null;

        $violations = new ConstraintViolationList([
            new ConstraintViolation('Item type can not be blank', null, [], '', 'type', ''),
            new ConstraintViolation('Invalid unit', null, [], '', 'unit', ''),
            new ConstraintViolation('This value should be of type string.', null, [], '', 'name', ''),
        ]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with(
                ['type' => $type, 'unit' => $unit, 'name' => $name],
                $this->isInstanceOf(Assert\Collection::class)
            )
            ->willReturn($violations);

        $result = $this->requestValidator->validListRequest($type, $unit, $name);

        $this->assertCount(3, $result);
        $this->assertContains('Item type can not be blank', $result);
        $this->assertContains('Invalid unit', $result);
        $this->assertContains('This value should be of type string.', $result);
    }

    public function testValidRemoveRequestWithValidData()
    {
        $type = SuperMarketGlobals::ITEM_TYPE_VEGETABLE->value;
        $id = 123;

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with(
                ['type' => $type, 'id' => $id],
                $this->isInstanceOf(Assert\Collection::class)
            )
            ->willReturn(new ConstraintViolationList());

        $result = $this->requestValidator->validRemoveRequest($type, $id);

        $this->assertEmpty($result);
    }

    public function testValidRemoveRequestWithInvalidData()
    {
        $type = '';
        $id = 'not an integer';

        $violations = new ConstraintViolationList([
            new ConstraintViolation('Item type can not be blank', null, [], '', 'type', ''),
            new ConstraintViolation('This value should be of type integer.', null, [], '', 'id', ''),
        ]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with(
                ['type' => $type, 'id' => $id],
                $this->isInstanceOf(Assert\Collection::class)
            )
            ->willReturn($violations);

        $result = $this->requestValidator->validRemoveRequest($type, $id);

        $this->assertCount(2, $result);
        $this->assertContains('Item type can not be blank', $result);
        $this->assertContains('This value should be of type integer.', $result);
    }

    public function testValidSearchRequestWithValidData()
    {
        $searchQuery = 'Apple';

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with(
                ['searchQuery' => $searchQuery],
                $this->isInstanceOf(Assert\Collection::class)
            )
            ->willReturn(new ConstraintViolationList());

        $result = $this->requestValidator->validSearchRequest($searchQuery);

        $this->assertEmpty($result);
    }

    public function testValidSearchRequestWithInvalidData()
    {
        $searchQuery = '';

        $violations = new ConstraintViolationList([
            new ConstraintViolation('Search query can not be blank', null, [], '', 'searchQuery', ''),
        ]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with(
                ['searchQuery' => $searchQuery],
                $this->isInstanceOf(Assert\Collection::class)
            )
            ->willReturn($violations);

        $result = $this->requestValidator->validSearchRequest($searchQuery);

        $this->assertCount(1, $result);
        $this->assertContains('Search query can not be blank', $result);
    }

    public function testValidAddRequestWithEmptyFields()
    {
        $item = [
            'type' => '',
            'name' => '',
            'quantity' => '',
            'unit' => '',
        ];

        $violations = new ConstraintViolationList([
            new ConstraintViolation('Item type can not be blank', null, [], '', 'type', ''),
            new ConstraintViolation('Item name can not be blank', null, [], '', 'name', ''),
            new ConstraintViolation('Item quantity can not be blank', null, [], '', 'quantity', ''),
            new ConstraintViolation('Item unit can not be blank', null, [], '', 'unit', ''),
        ]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($item, $this->isInstanceOf(Assert\Collection::class))
            ->willReturn($violations);

        $result = $this->requestValidator->validAddRequest($item);

        $this->assertCount(4, $result);
    }

    public function testValidAddRequestWithBoundaryValues()
    {
        $item = [
            'type' => SuperMarketGlobals::ITEM_TYPE_FRUIT->value,
            'name' => 'Apple',
            'quantity' => PHP_INT_MAX,
            'unit' => SuperMarketGlobals::WEIGHT_GRAMS->value,
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($item, $this->isInstanceOf(Assert\Collection::class))
            ->willReturn(new ConstraintViolationList());

        $result = $this->requestValidator->validAddRequest($item);

        $this->assertEmpty($result);
    }

    public function testValidListRequestWithMissingFields()
    {
        $type = '';
        $unit = '';
        $name = '';

        $violations = new ConstraintViolationList([
            new ConstraintViolation('Item type can not be blank', null, [], '', 'type', ''),
            new ConstraintViolation('Invalid unit', null, [], '', 'unit', ''),
            new ConstraintViolation('This value should be of type string.', null, [], '', 'name', ''),
        ]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with(
                ['type' => $type, 'unit' => $unit, 'name' => $name],
                $this->isInstanceOf(Assert\Collection::class)
            )
            ->willReturn($violations);

        $result = $this->requestValidator->validListRequest($type, $unit, $name);

        $this->assertCount(3, $result);
    }

    public function testValidRemoveRequestWithInvalidIdType()
    {
        $type = SuperMarketGlobals::ITEM_TYPE_VEGETABLE->value;
        $id = 'not-an-integer';

        $violations = new ConstraintViolationList([
            new ConstraintViolation('This value should be of type integer.', null, [], '', 'id', ''),
        ]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with(
                ['type' => $type, 'id' => $id],
                $this->isInstanceOf(Assert\Collection::class)
            )
            ->willReturn($violations);

        $result = $this->requestValidator->validRemoveRequest($type, $id);

        $this->assertCount(1, $result);
        $this->assertContains('This value should be of type integer.', $result);
    }

    public function testValidSearchRequestWithNullValue()
    {
        $searchQuery = null;

        $violations = new ConstraintViolationList([
            new ConstraintViolation('Search query can not be blank', null, [], '', 'searchQuery', ''),
        ]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with(
                ['searchQuery' => $searchQuery],
                $this->isInstanceOf(Assert\Collection::class)
            )
            ->willReturn($violations);

        $result = $this->requestValidator->validSearchRequest($searchQuery);

        $this->assertCount(1, $result);
        $this->assertContains('Search query can not be blank', $result);
    }

    public function testValidAddRequestWithExtraFields()
    {
        $item = [
            'type' => SuperMarketGlobals::ITEM_TYPE_FRUIT->value,
            'name' => 'Apple',
            'quantity' => 10,
            'unit' => SuperMarketGlobals::WEIGHT_GRAMS->value,
            'extraField' => 'extraValue',
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($item, $this->isInstanceOf(Assert\Collection::class))
            ->willReturn(new ConstraintViolationList());

        $result = $this->requestValidator->validAddRequest($item);

        $this->assertEmpty($result);
    }

    public function testValidListRequestWithEmptyUnit()
    {
        $type = SuperMarketGlobals::ITEM_TYPE_FRUIT->value;
        $unit = '';
        $name = 'Apple';

        $violations = new ConstraintViolationList([
            new ConstraintViolation('Invalid unit', null, [], '', 'unit', ''),
        ]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with(
                ['type' => $type, 'unit' => $unit, 'name' => $name],
                $this->isInstanceOf(Assert\Collection::class)
            )
            ->willReturn($violations);

        $result = $this->requestValidator->validListRequest($type, $unit, $name);

        $this->assertCount(1, $result);
    }

    public function testValidRemoveRequestWithEmptyType()
    {
        $type = '';
        $id = 123;

        $violations = new ConstraintViolationList([
            new ConstraintViolation('Item type can not be blank', null, [], '', 'type', ''),
        ]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with(
                ['type' => $type, 'id' => $id],
                $this->isInstanceOf(Assert\Collection::class)
            )
            ->willReturn($violations);

        $result = $this->requestValidator->validRemoveRequest($type, $id);

        $this->assertCount(1, $result);
    }
}
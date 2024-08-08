<?php

namespace App\Tests\App\Service;

use App\Service\ElasticsearchService;
use Elasticsearch\Client;
use Elasticsearch\Namespaces\IndicesNamespace;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ElasticsearchServiceTest extends TestCase
{
    /** @var Client|MockObject */
    private $elasticsearchClient;

    /** @var ElasticsearchService */
    private $elasticsearchService;

    /** @var IndicesNamespace|MockObject */
    private $indicesNamespace;

    protected function setUp(): void
    {
        $this->indicesNamespace = $this->createMock(IndicesNamespace::class);
        $this->elasticsearchClient = $this->createMock(Client::class);
        $this->elasticsearchService = new ElasticsearchService($this->elasticsearchClient);
    }

    protected function tearDown(): void
    {
        $this->indicesNamespace = null;
        $this->elasticsearchClient = null;
        $this->elasticsearchService = null;
    }

    public function testAddCreatesIndexIfNotExists()
    {
        $item = ['type' => 'fruit', 'name' => 'Apple', 'quantity' => 10];

        $this->elasticsearchClient
            ->method('indices')
            ->willReturn($this->indicesNamespace);

        $this->indicesNamespace
            ->expects($this->once())
            ->method('exists')
            ->with(['index' => 'fruit'])
            ->willReturn(false);
        $this->indicesNamespace
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($params) {
                return $params['index'] === 'fruit' &&
                    isset($params['body']['mappings']['properties']['name']['type']) &&
                    $params['body']['mappings']['properties']['name']['type'] === 'text';
            }));
        $this->elasticsearchClient
            ->expects($this->once())
            ->method('index')
            ->with(['index' => 'fruit', 'body' => $item])
            ->willReturn(['result' => 'created']);

        $response = $this->elasticsearchService->add($item);

        $this->assertEquals(['result' => 'created'], $response);
    }

    public function testAddToExistingIndex()
    {
        $item = ['type' => 'fruit', 'name' => 'Apple', 'quantity' => 10];

        $this->elasticsearchClient
            ->method('indices')
            ->willReturn($this->indicesNamespace);

        $this->indicesNamespace
            ->expects($this->once())
            ->method('exists')
            ->with(['index' => 'fruit'])
            ->willReturn(true);
        $this->indicesNamespace
            ->expects($this->never())
            ->method('create');
        $this->elasticsearchClient
            ->expects($this->once())
            ->method('index')
            ->with(['index' => 'fruit', 'body' => $item])
            ->willReturn(['result' => 'created']);

        $response = $this->elasticsearchService->add($item);

        $this->assertEquals(['result' => 'created'], $response);
    }

    public function testRemoveItemFromExistingIndex()
    {
        $type = 'fruit';
        $name = 'Apple';

        $this->elasticsearchClient
            ->method('indices')
            ->willReturn($this->indicesNamespace);

        $this->indicesNamespace
            ->expects($this->once())
            ->method('exists')
            ->with(['index' => $type])
            ->willReturn(true);
        $this->elasticsearchClient
            ->expects($this->once())
            ->method('deleteByQuery')
            ->with($this->callback(function ($params) use ($type, $name) {
                return $params['index'] === $type &&
                    $params['body']['query']['wildcard']['name'] === $name;
            }))
            ->willReturn(['deleted' => 1]);

        $response = $this->elasticsearchService->remove($type, $name);

        $this->assertEquals(['deleted' => 1], $response);
    }

    public function testRemoveItemFromNonExistingIndex()
    {
        $type = 'fruit';
        $name = 'Apple';

        $this->elasticsearchClient
            ->method('indices')
            ->willReturn($this->indicesNamespace);

        $this->indicesNamespace
            ->expects($this->once())
            ->method('exists')
            ->with(['index' => $type])
            ->willReturn(false);
        $this->elasticsearchClient
            ->expects($this->never())
            ->method('deleteByQuery');

        $response = $this->elasticsearchService->remove($type, $name);

        $this->assertNull($response);
    }

    public function testSearchByName()
    {
        $name = 'Apple';
        $expectedResponse = [
            'hits' => [
                'hits' => [
                    ['_source' => ['type' => 'fruit', 'name' => 'Apple', 'quantity' => 10]]
                ]
            ]
        ];

        $this->elasticsearchClient
            ->expects($this->once())
            ->method('search')
            ->with($this->callback(function ($params) use ($name) {
                return $params['index'] === '_all' &&
                    $params['body']['query']['wildcard']['name'] === '*' . $name . '*';
            }))
            ->willReturn($expectedResponse);

        $response = $this->elasticsearchService->searchByName($name);

        $this->assertEquals([['type' => 'fruit', 'name' => 'Apple', 'quantity' => 10]], $response);
    }

    public function testAddWithMissingFields()
    {
        $item = ['type' => 'fruit', 'name' => 'Apple'];

        $this->elasticsearchClient
            ->method('indices')
            ->willReturn($this->indicesNamespace);

        $this->indicesNamespace
            ->expects($this->once())
            ->method('exists')
            ->with(['index' => 'fruit'])
            ->willReturn(false);
        $this->indicesNamespace
            ->expects($this->once())
            ->method('create');
        $this->elasticsearchClient
            ->expects($this->once())
            ->method('index')
            ->with(['index' => 'fruit', 'body' => $item])
            ->willReturn(['result' => 'created']);

        $response = $this->elasticsearchService->add($item);

        $this->assertEquals(['result' => 'created'], $response);
    }

    public function testAddWithInvalidDataTypes()
    {
        $item = ['type' => 'fruit', 'name' => 'Apple', 'quantity' => 'ten'];

        $this->elasticsearchClient
            ->method('indices')
            ->willReturn($this->indicesNamespace);

        $this->indicesNamespace
            ->expects($this->once())
            ->method('exists')
            ->with(['index' => 'fruit'])
            ->willReturn(false);
        $this->indicesNamespace
            ->expects($this->once())
            ->method('create');
        $this->elasticsearchClient
            ->expects($this->once())
            ->method('index')
            ->with(['index' => 'fruit', 'body' => $item])
            ->willReturn(['result' => 'created']);

        $response = $this->elasticsearchService->add($item);

        $this->assertEquals(['result' => 'created'], $response);
    }

    public function testRemoveItemWithWildcardName()
    {
        $type = 'fruit';
        $name = 'App*';

        $this->elasticsearchClient
            ->method('indices')
            ->willReturn($this->indicesNamespace);

        $this->indicesNamespace
            ->expects($this->once())
            ->method('exists')
            ->with(['index' => $type])
            ->willReturn(true);
        $this->elasticsearchClient
            ->expects($this->once())
            ->method('deleteByQuery')
            ->with($this->callback(function ($params) use ($type, $name) {
                return $params['index'] === $type &&
                    $params['body']['query']['wildcard']['name'] === $name;
            }))
            ->willReturn(['deleted' => 1]);

        $response = $this->elasticsearchService->remove($type, $name);

        $this->assertEquals(['deleted' => 1], $response);
    }

    public function testSearchForNonExistingItem()
    {
        $name = 'NonExistingItem';

        $this->elasticsearchClient
            ->expects($this->once())
            ->method('search')
            ->with($this->callback(function ($params) use ($name) {
                return $params['index'] === '_all' &&
                    $params['body']['query']['wildcard']['name'] === '*' . $name . '*';
            }))
            ->willReturn(['hits' => ['hits' => []]]);

        $response = $this->elasticsearchService->searchByName($name);

        $this->assertEquals([], $response);
    }

    public function testSearchByNameWithSpecialCharacters()
    {
        $name = 'App!e';
        $expectedResponse = [
            'hits' => [
                'hits' => [
                    ['_source' => ['type' => 'fruit', 'name' => 'App!e', 'quantity' => 10]]
                ]
            ]
        ];

        $this->elasticsearchClient
            ->expects($this->once())
            ->method('search')
            ->with($this->callback(function ($params) use ($name) {
                return $params['index'] === '_all' &&
                    $params['body']['query']['wildcard']['name'] === '*' . $name . '*';
            }))
            ->willReturn($expectedResponse);

        $response = $this->elasticsearchService->searchByName($name);

        $this->assertEquals([['type' => 'fruit', 'name' => 'App!e', 'quantity' => 10]], $response);
    }
}
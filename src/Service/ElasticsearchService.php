<?php

namespace App\Service;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Exception;

class ElasticsearchService
{
    public function __construct(
        private Client $elasticsearchClient
    )
    {
        //
    }

    public function add(array $item)
    {
        $this->checkClusterHealth();

        $index = $item['type'];

        // Check if the index exists
        if (!$this->elasticsearchClient->indices()->exists(['index' => $index])) {
            // Create the index if it does not exist
            $params = [
                'index' => $index,
                'body' => [
                    'mappings' => [
                        'properties' => [
                            'name' => ['type' => 'text'],
                            'quantity' => ['type' => 'integer'],
                        ]
                    ]
                ]
            ];
            $this->elasticsearchClient->indices()->create($params);
        }

        // Add data to the index
        $params = [
            'index' => $index,
            'body' => $item
        ];
        $response = $this->elasticsearchClient->index($params);

        return $response;
    }

    public function checkClusterHealth()
    {
        try {
            $health = $this->elasticsearchClient->cluster()->health();
            return $health;
        } catch (NoNodesAvailableException $e) {
            throw new Exception('No alive nodes found in the cluster');
        }
    }

    public function remove($type, $name)
    {
        $this->checkClusterHealth();

        // Check if the index exists
        if ($this->elasticsearchClient->indices()->exists(['index' => $type])) {
            // Delete the entry by name
            $params = [
                'index' => $type,
                'body' => [
                    'query' => [
                        'wildcard' => [
                            'name' => $name
                        ]
                    ]
                ]
            ];

            $deleteResponse = $this->elasticsearchClient->deleteByQuery($params);

            return $deleteResponse;

        }

        return null;
    }

    public function searchByName(string $name)
    {
        $this->checkClusterHealth();

        $params = [
            'index' => '_all',
            'body' => [
                'query' => [
                    'wildcard' => [
                        'name' => '*' . $name . '*'
                    ]
                ]
            ]
        ];

        $response = $this->elasticsearchClient->search($params);

        return array_map(fn($hit) => $hit['_source'], $response['hits']['hits']);
    }
}
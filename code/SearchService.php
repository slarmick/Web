<?php
require_once 'vendor/autoload.php';

use GuzzleHttp\Client;

class SearchService {
    private $client;

    public function __construct() {
        $this->client = new Client([
            'base_uri' => 'http://elasticsearch:9200/',
            'timeout'  => 5.0,
        ]);
        $this->initIndex();
    }

    private function initIndex() {
        try {
            // Создаем индекс для пользователей, если не существует
            $this->client->put('masterclass_users', [
                'json' => [
                    'mappings' => [
                        'properties' => [
                            'name' => ['type' => 'text'],
                            'email' => ['type' => 'keyword'],
                            'topic' => ['type' => 'keyword'],
                            'format' => ['type' => 'keyword'],
                            'birthdate' => ['type' => 'date'],
                            'materials' => ['type' => 'keyword'],
                            'created_at' => ['type' => 'date']
                        ]
                    ]
                ]
            ]);
        } catch (Exception $e) {
            // Индекс уже существует - игнорируем ошибку
        }
    }

    public function indexUser($id, $userData) {
        try {
            $response = $this->client->put("masterclass_users/_doc/$id", [
                'json' => $userData
            ]);
            return json_decode($response->getBody(), true);
        } catch (Exception $e) {
            error_log("Elasticsearch indexing error: " . $e->getMessage());
            return false;
        }
    }

    public function searchUsers($query) {
        try {
            $response = $this->client->get("masterclass_users/_search", [
                'json' => [
                    'query' => [
                        'multi_match' => [
                            'query' => $query,
                            'fields' => ['name', 'email', 'topic']
                        ]
                    ]
                ]
            ]);
            
            $result = json_decode($response->getBody(), true);
            return $result['hits']['hits'] ?? [];

        } catch (Exception $e) {
            error_log("Elasticsearch search error: " . $e->getMessage());
            return [];
        }
    }

    public function getAggregations() {
        try {
            $response = $this->client->get("masterclass_users/_search", [
                'json' => [
                    'size' => 0,
                    'aggs' => [
                        'topics' => ['terms' => ['field' => 'topic']],
                        'formats' => ['terms' => ['field' => 'format']],
                        'materials' => ['terms' => ['field' => 'materials']]
                    ]
                ]
            ]);
            
            $result = json_decode($response->getBody(), true);
            return $result['aggregations'] ?? [];

        } catch (Exception $e) {
            error_log("Elasticsearch aggregation error: " . $e->getMessage());
            return [];
        }
    }
}
?>
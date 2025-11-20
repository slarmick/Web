<?php
require_once 'vendor/autoload.php';

class ElasticsearchService {
    private $client;
    public $isConnected = false;

    public function __construct() {
        try {
            $this->client = new GuzzleHttp\Client([
                'base_uri' => 'http://elasticsearch:9200/',
                'timeout' => 5.0
            ]);
            
            // Проверяем подключение
            $response = $this->client->get('');
            $this->isConnected = $response->getStatusCode() === 200;
        } catch (Exception $e) {
            error_log("Elasticsearch connection failed: " . $e->getMessage());
            $this->isConnected = false;
        }
    }

    // Индексация данных регистрации для поиска
    public function indexRegistration($registrationData) {
        if (!$this->isConnected) return false;

        try {
            $response = $this->client->post('registrations/_doc', [
                'json' => [
                    'name' => $registrationData['name'],
                    'email' => $registrationData['email'],
                    'topic' => $registrationData['topic'],
                    'format' => $registrationData['format'],
                    'materials' => $registrationData['materials'],
                    'birthdate' => $registrationData['birthdate'],
                    'created_at' => date('Y-m-d\TH:i:s\Z'),
                    'timestamp' => time()
                ]
            ]);
            
            return $response->getStatusCode() === 201;
        } catch (Exception $e) {
            error_log("Elasticsearch indexing failed: " . $e->getMessage());
            return false;
        }
    }

    // Поиск по регистрациям
    public function searchRegistrations($query) {
        if (!$this->isConnected) return [];

        try {
            $response = $this->client->post('registrations/_search', [
                'json' => [
                    'query' => [
                        'multi_match' => [
                            'query' => $query,
                            'fields' => ['name', 'email', 'topic^2'],
                            'fuzziness' => 'AUTO'
                        ]
                    ],
                    'size' => 50
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['hits']['hits'] ?? [];
        } catch (Exception $e) {
            error_log("Elasticsearch search failed: " . $e->getMessage());
            return [];
        }
    }

    // Аналитика по темам мастер-классов
    public function getTopicAnalytics() {
        if (!$this->isConnected) return [];

        try {
            $response = $this->client->post('registrations/_search', [
                'json' => [
                    'size' => 0,
                    'aggs' => [
                        'topics' => [
                            'terms' => [
                                'field' => 'topic.keyword',
                                'size' => 10
                            ]
                        ],
                        'formats' => [
                            'terms' => [
                                'field' => 'format.keyword'
                            ]
                        ],
                        'materials' => [
                            'terms' => [
                                'field' => 'materials.keyword'
                            ]
                        ]
                    ]
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['aggregations'] ?? [];
        } catch (Exception $e) {
            error_log("Elasticsearch analytics failed: " . $e->getMessage());
            return [];
        }
    }

    // Создание индекса при первом запуске
    public function createIndex() {
        if (!$this->isConnected) return false;

        try {
            $this->client->put('registrations', [
                'json' => [
                    'mappings' => [
                        'properties' => [
                            'name' => ['type' => 'text'],
                            'email' => ['type' => 'keyword'],
                            'topic' => ['type' => 'text'],
                            'format' => ['type' => 'keyword'],
                            'materials' => ['type' => 'keyword'],
                            'birthdate' => ['type' => 'date'],
                            'created_at' => ['type' => 'date'],
                            'timestamp' => ['type' => 'long']
                        ]
                    ]
                ]
            ]);
            return true;
        } catch (Exception $e) {
            // Индекс уже существует - это нормально
            return true;
        }
    }
}
?>
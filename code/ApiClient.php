<?php
require_once __DIR__ . '/../vendor/autoload.php';
use GuzzleHttp\Client;

class ApiClient {
    private Client $client;

    public function __construct() {
        $this->client = new Client();
    }

    public function getArtTechniques(): array {
        try {
            $response = $this->client->get('https://api.artic.edu/api/v1/artworks', [
                'query' => [
                    'limit' => 10,
                    'fields' => 'id,title,artist_display,date_display,medium_display,image_id'
                ]
            ]);
            
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);
            
            return [
                'success' => true,
                'data' => $data['data'] ?? [],
                'info' => $data['info'] ?? []
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getArtworkById(int $id): array {
        try {
            $response = $this->client->get("https://api.artic.edu/api/v1/artworks/{$id}");
            $body = $response->getBody()->getContents();
            return json_decode($body, true);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
?>
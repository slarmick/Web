<?php
require_once __DIR__ . '/vendor/autoload.php';
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
                    'fields' => 'id,title,artist_display,date_display,medium_display,image_id,thumbnail',
                    'has_images' => 1  // Только с изображениями!
                ]
            ]);
            
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);
            
            // Обрабатываем artworks чтобы получить правильные URL изображений
            $processedArtworks = [];
            foreach ($data['data'] ?? [] as $artwork) {
                $imageUrl = $this->getImageUrl($artwork);
                $artwork['image_url'] = $imageUrl;
                $processedArtworks[] = $artwork;
            }
            
            return [
                'success' => true,
                'data' => $processedArtworks,
                'info' => $data['info'] ?? []
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function getImageUrl(array $artwork): string {
        // Способ 1: Если есть image_id, используем IIIF URL
        if (!empty($artwork['image_id'])) {
            return "https://www.artic.edu/iiif/2/{$artwork['image_id']}/full/300,/0/default.jpg";
        }
        
        // Способ 2: Если есть thumbnail
        if (!empty($artwork['thumbnail']) && !empty($artwork['thumbnail']['lqip'])) {
            return $artwork['thumbnail']['lqip'];
        }
        
        // Способ 3: Заглушка
        return 'https://via.placeholder.com/300x200/3498db/ffffff?text=No+Image+Available';
    }
}
?>
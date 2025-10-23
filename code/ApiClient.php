<?php
require_once __DIR__ . '/vendor/autoload.php';
use GuzzleHttp\Client;

class ApiClient {
    private Client $client;

    public function __construct() {
        $this->client = new Client();
    }

    /**
     * Получает список художественных техник из API
     */
    public function getArtTechniques(): array {
        try {
            $response = $this->client->get('https://api.artic.edu/api/v1/artworks', [
                'query' => [
                    'limit' => 100,
                    'fields' => 'id,title,artist_display,date_display,medium_display,image_id,thumbnail',
                    'has_images' => 1
                ]
            ]);
            
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);
            
            // Извлекаем уникальные художественные техники
            $techniques = [];
            $artworksByTechnique = [];
            
            foreach ($data['data'] ?? [] as $artwork) {
                $technique = $artwork['medium_display'] ?? null;
                if ($technique && !in_array($technique, $techniques)) {
                    $techniques[] = $technique;
                    $artworksByTechnique[$technique] = $artwork;
                }
                
                // Ограничиваем количество техник
                if (count($techniques) >= 15) {
                    break;
                }
            }
            
            return [
                'success' => true,
                'techniques' => $techniques,
                'artworks_by_technique' => $artworksByTechnique,
                'all_artworks' => $data['data'] ?? []
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Получает примеры работ для конкретной техники
     */
    public function getArtworksByTechnique(string $technique): array {
        try {
            $response = $this->client->get('https://api.artic.edu/api/v1/artworks', [
                'query' => [
                    'limit' => 5,
                    'fields' => 'id,title,artist_display,date_display,medium_display,image_id,thumbnail',
                    'has_images' => 1,
                    'query' => [
                        'term' => [
                            'medium_display' => $technique
                        ]
                    ]
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
                'technique' => $technique,
                'artworks' => $processedArtworks
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
<?php
class ApiClient {
    public function request(string $url): array {
        try {
            // Прямой вызов API без демо-данных
            $response = file_get_contents($url, false, stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
                'http' => [
                    'timeout' => 15,
                    'ignore_errors' => true
                ]
            ]));
            
            if ($response === false) {
                throw new Exception('Не удалось подключиться к API');
            }
            
            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Ошибка декодирования JSON: ' . json_last_error_msg());
            }
            
            if (!isset($data['data'])) {
                throw new Exception('Некорректный ответ от API');
            }
            
            return $data;
            
        } catch (\Exception $e) {
            error_log("API Error: " . $e->getMessage());
            // ВОЗВРАЩАЕМ ПУСТОЙ МАССИВ вместо демо-данных
            return [
                'data' => [],
                'pagination' => ['total' => 0]
            ];
        }
    }
}
?>
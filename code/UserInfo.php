<?php
class UserInfo {
    public static function getInfo(): array {
        return [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Неизвестно',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Неизвестно',
            'server_time' => date('Y-m-d H:i:s'),
            'last_submission' => $_COOKIE['last_submission'] ?? 'Еще не было'
        ];
    }

    public static function getBrowserInfo(): string {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (strpos($user_agent, 'Chrome') !== false) return 'Google Chrome';
        if (strpos($user_agent, 'Firefox') !== false) return 'Mozilla Firefox';
        if (strpos($user_agent, 'Safari') !== false) return 'Apple Safari';
        if (strpos($user_agent, 'Edge') !== false) return 'Microsoft Edge';
        
        return 'Другой браузер';
    }
}
?>
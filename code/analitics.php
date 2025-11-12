<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Аналитика - Лабораторные работы</title>
    <style>
        body { 
            font-family: 'Arial', sans-serif; 
            max-width: 1000px; 
            margin: 0 auto; 
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin: 40px 0;
        }
        h1 { 
            color: #2c3e50; 
            text-align: center;
            margin-bottom: 30px;
        }
        .nav-buttons {
            display: flex;
            gap: 15px;
            margin: 30px 0;
            flex-wrap: wrap;
            justify-content: center;
        }
        .nav-button {
            background: #3498db;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
            border: 2px solid #3498db;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            border-left: 4px solid #3498db;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }
        .stat-label {
            color: #7f8c8d;
        }
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📈 Аналитика регистраций</h1>

        <div class="nav-buttons">
            <a href="/" class="nav-button">🏠 Главная</a>
            <a href="/master-class.html" class="nav-button">📚 Форма регистрации</a>
            <a href="/view.php" class="nav-button">📊 Все данные</a>
        </div>

        <?php
        require_once 'MasterClassRegistration.php';
        
        try {
            $registration = new MasterClassRegistration();
            $stats = $registration->getRegistrationStats();
            
            $total = $stats['total'] ?? 0;
            $today = $stats['today'] ?? 0;
            $uniqueEmails = $registration->getUniqueEmails();
            $avgPerDay = $registration->getAverageRegistrationsPerDay();
            
        } catch (Exception $e) {
            $total = $today = $uniqueEmails = $avgPerDay = 0;
            $stats = [];
        }
        ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $total ?></div>
                <div class="stat-label">Всего регистраций</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $today ?></div>
                <div class="stat-label">Сегодня</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $uniqueEmails ?></div>
                <div class="stat-label">Уникальных email</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $avgPerDay ?></div>
                <div class="stat-label">В день (среднее)</div>
            </div>
        </div>

        <?php if(!empty($stats['by_topic'])): ?>
        <div class="chart-container">
            <h3>📊 Распределение по темам</h3>
            <?php foreach($stats['by_topic'] as $topic): ?>
                <?php
                $topicNames = [
                    'webdev' => 'Веб-разработка',
                    'design' => 'UI/UX дизайн',
                    'marketing' => 'Цифровой маркетинг',
                    'data' => 'Анализ данных',
                    'mobile' => 'Мобильная разработка'
                ];
                $topicName = $topicNames[$topic['topic']] ?? $topic['topic'];
                $percentage = $total > 0 ? round(($topic['count'] / $total) * 100, 1) : 0;
                ?>
                <div style="margin: 10px 0;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span><?= $topicName ?></span>
                        <span><?= $topic['count'] ?> (<?= $percentage ?>%)</span>
                    </div>
                    <div style="background: #ecf0f1; border-radius: 5px; height: 20px;">
                        <div style="background: #3498db; height: 100%; border-radius: 5px; width: <?= $percentage ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if(!empty($stats['by_format'])): ?>
        <div class="chart-container">
            <h3>💻 Форматы участия</h3>
            <?php foreach($stats['by_format'] as $format): ?>
                <div style="margin: 10px 0;">
                    <div style="display: flex; justify-content: between; margin-bottom: 5px;">
                        <span><?= $format['format'] == 'online' ? '🎥 Онлайн' : '🏢 Очно' ?></span>
                        <span><?= $format['count'] ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
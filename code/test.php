<?php
echo "<h1>✅ PHP работает!</h1>";
echo "<p><strong>Версия PHP:</strong> " . phpversion() . "</p>";
echo "<p><strong>Время сервера:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Загруженные модули:</strong> " . implode(', ', get_loaded_extensions()) . "</p>";
echo '<br><a href="/">← На главную</a>';

# Лабораторная работа №1
## Веб-сервер в Docker (Nginx + HTML)

**Цель:** Научиться поднимать веб-сервер Nginx в контейнере Docker

### Выполненные этапы:
- ✅ Настройка базового nginx в Docker
- ✅ Создание кастомной HTML страницы
- ✅ Настройка volumes для live-обновлений
- ✅ Добавление дополнительных страниц
- ✅ Эксперименты с портами
- ✅ Добавление навигации между страницами

### Запуск проекта:
git clone <https://github.com/slarmick/Web>
cd nginx-lab

### Запустить контейнеры:
docker-compose up -d --build

Открыть в браузере: http://localhost:3000 📂 Содержимое проекта
docker-compose.yml — описание сервиса Nginx
code/index.html — главная HTML-страница
screenshots/ — все скриншоты
📸 Скриншоты работы
"D:\Git\Web\nginx-lab\screenshots\01_welcome_nginx.png"
"D:\Git\Web\nginx-lab\screenshots\02_custom_page.png"
"D:\Git\Web\nginx-lab\screenshots\03_about_page.png"
"D:\Git\Web\nginx-lab\screenshots\04_added_navigation.png"
"D:\Git\Web\nginx-lab\screenshots\05_new_port.png"

✅ Результат Сервер в Docker успешно запущен, Nginx отдаёт мою HTML-страницу.

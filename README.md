# Лабораторная работа №1
## Веб-сервер в Docker (Nginx + HTML)

## 👩‍💻 Автор
ФИО: Здановский Даниил Станиславович
Группа: 3МО-РИСКУ

**Цель:** Научиться поднимать веб-сервер Nginx в контейнере Docker

### Выполненные этапы:

#### Этап 1: Первый запуск Nginx
![Welcome to Nginx](screenshots/01_welcome_nginx.png)

#### Этап 2: Кастомная страница
![Custom Page](screenshots/02_custom_page.png)

#### Этап 3: Страница "О нас"
![About Page](screenshots/03_about_page.png)

#### Этап 4: Добавление навигации
![Navigation](screenshots/04_added_navigation.png)

#### Этап 5: Работа на порту 3000
![New port](screenshots/05_new_port.png)

### Запуск проекта:
git clone <https://github.com/slarmick/Web>
cd nginx-lab

### Запустить контейнеры:
docker-compose up -d --build

Открыть в браузере: http://localhost:3000 📂 Содержимое проекта
docker-compose.yml — описание сервиса Nginx
code/index.html — главная HTML-страница
screenshots/ — все скриншоты

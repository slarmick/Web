# Лабораторная работа №1: Nginx + Docker

## 👩‍💻 Автор
ФИО: Здановский Даниил Станиславович
Группа: 3МО-РИСКУ

---

## 📌 Описание задания
Создать веб-сервер в Docker с использованием Nginx и подключить HTML-страницу.  
Результат доступен по адресу [http://localhost:3000](http://localhost:3000).

---

## ⚙️ Как запустить проект

1. Клонировать репозиторий:
   ```bash
   git clone <https://github.com/slarmick/Web>
   cd nginx-lab
Запустить контейнеры:
```bash
docker-compose up -d --build
```
Открыть в браузере:
```http://localhost:3000```
📂 Содержимое проекта

```docker-compose.yml``` — описание сервиса Nginx

```code/index.html``` — главная HTML-страница

```screenshots/``` — все скриншоты

📸 Скриншоты работы
Этап 1.
<img width="624" height="118" alt="Рисунок6" src="https://github.com/user-attachments/assets/e022adb2-f254-4b2d-b1f4-8c41bb8b6c1e" />

Этап 2.
<img width="624" height="156" alt="Рисунок5" src="https://github.com/user-attachments/assets/34a0b23d-4734-4586-9049-1dc2e74f988a" />

Этап 3.
<img width="624" height="156" alt="Рисунок4" src="https://github.com/user-attachments/assets/5d6aaf47-1a0f-4dcc-bd9e-772be632b1ce" />

Этап 4.
<img width="624" height="231" alt="Рисунок3" src="https://github.com/user-attachments/assets/0f4d7f66-3c72-494b-a618-3838af0886d6" />
<img width="624" height="283" alt="Рисунок2" src="https://github.com/user-attachments/assets/3a904c8c-3d2c-4fb5-a214-a4b85e6393bb" />
<img width="624" height="239" alt="Рисунок1" src="https://github.com/user-attachments/assets/03272d24-fe72-4193-a20d-46b80b2b5103" />


✅ Результат
Сервер в Docker успешно запущен, Nginx отдаёт мою HTML-страницу.

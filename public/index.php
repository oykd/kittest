<?php

// Загружаем базовые настройки
require_once __DIR__ . '/../configs/main.conf.php';

// Автозагрузка классов
// Стандартная библиотека PHP (SPL, PHP >= 5.1)
spl_autoload_register(function ($className) {
    $class = __DIR__ . '/../app/' . str_replace('\\', '/', $className) . '.php';
    if (file_exists($class)) require_once($class);
});

// Запускаем сайт
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->run();
<?php

DEFINE('ROOT', realpath(__DIR__ . '/../'));

// В рамках данной задачи зависимости работают по шаблону Singleton и не передаются через конструктор
$app = new Application();

// Перед запуском можно выполнить дополнительные действия с экземпляром приложения

return $app;
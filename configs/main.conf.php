<?php

// Устанавливаем UTF-8 кодировкой по умолчанию
ini_set('default_charset', 'utf-8');

// Отображаем ошибки и предупреждения
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Отлавливаем предупреждения как ошибки
set_error_handler(function ($errno, $errstr, $errfile, $errline, $errcontext) {
    if (0 === error_reporting()) return false;
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});


DEFINE('PROJECT_NAME',  'Tree test for kit');
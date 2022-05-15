<?php

namespace Controllers;

return [
    [
        'roles' => ['guest', 'admin'],
        'method' => 'GET',
        'path' => '/',
        'controller' => MainPageController::class,
        'action' => 'render',
        'id' => 0,
    ],
    [
        'roles' => ['admin'],
        'method' => 'GET',
        'path' => '/admin',
        'controller' => AdminPageController::class,
        'action' => 'render',
        'id' => 1,
    ],
    [
        'roles' => ['guest'],
        'method' => 'POST',
        'path' => '/login',
        'controller' => AuthController::class,
        'action' => 'login',
        'id' => 2,
    ],
    [
        'roles' => ['admin'],
        'method' => ['GET', 'POST'],
        'path' => '/logout',
        'controller' => AuthController::class,
        'action' => 'logout',
        'id' => 3,
    ],
    [
        'roles' => ['guest', 'admin'],
        'method' => 'GET',
        'path' => '/tree',
        'controller' => TreeController::class,
        'action' => 'tree',
        'id' => 4,
    ],
    [
        'roles' => ['guest', 'admin'],
        'method' => 'GET',
        'path' => '#^\/tree\/(?<id>[0-9]+)$#',
        'controller' => TreeController::class,
        'action' => 'leaf',
        'id' => 5,
    ],
    [
        'roles' => ['admin'],
        'method' => 'POST',
        'path' => '/save',
        'controller' => TreeController::class,
        'action' => 'save',
        'id' => 6,
    ],
    [
        'roles' => ['admin'],
        'method' => 'POST',
        'path' => '/delete',
        'controller' => TreeController::class,
        'action' => 'delete',
        'id' => 7,
    ],
    [
        'roles' => ['admin'],
        'method' => 'POST',
        'path' => '/register',
        'controller' => AuthController::class,
        'action' => 'register',
        'id' => 8,
    ],

];
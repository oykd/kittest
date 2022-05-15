<?php

namespace Controllers;

use Exceptions\AdminException as Fail;
use \Models\Admin;

/**
 * Class AuthController
 * @package Controllers
 */
class AuthController extends BaseController
{
    /**
     * /login
     *
     * @param int $route_id
     * @param array $data
     * @throws \Exceptions\AdminException | \Exceptions\DBException
     */
    public function login($route_id, $data)
    {
        Admin::get()->login($data['login'], $data['password']);
        echo json_encode([
            'code' => 0,
            'message' => 'ok',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * /logout
     */
    public function logout()
    {
        Admin::get()->logout();
    }

    /**
     * /register
     *
     * @param int $route_id
     * @param array $data
     * @throws \Exceptions\AdminException | \Exceptions\DBException
     */
    public function register($route_id, $data)
    {
        if (!isset($data['login'], $data['password']))
            throw Fail::incorrectInputData();

        Admin::get()->register($data['login'], $data['password']);
        echo json_encode([
            'code' => 0,
            'message' => 'Пользователь добавлен',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
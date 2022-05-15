<?php

namespace Router;

use \Exceptions\RouterException as Fail;
use Exceptions\TreeException;

/**
 * Class Router
 * @package Router
 */
class Router
{
    // Паттерн Singleton
    use \Singleton;

    /** @var array */
    protected $routes;

    /**
     * Синоним getInstance
     *
     * @return Router
     */
    public static function get()
    {
        /** @var Router $instance */
        $instance = self::getInstance();
        return $instance;
    }

    /**
     * Сохраняем маршруты в экземпляр класса
     *
     * @param array $routes
     * @return Router
     */
    public static function init($routes)
    {
        $instance = self::get();
        $instance->routes = $routes;

        return $instance;
    }

    /**
     * Запускаем марштрутизатор, ищем подходящий контроллер
     *
     * @param string $role
     * @throws \Exceptions\RouterException | \Exceptions\TreeException
     */
    public function launch($role)
    {
        // ищем подходящий маршрут
        foreach ($this->routes as $route) {

            // в старых версиях php list() не работает с ключами, поэтому так
            list($roles, $method, $path, $controller, $action, $id) = array_values($route);

            // проверяем uri
            if (isset($path[0]) && $path[0] == '#') {
                $request_uri = $_SERVER['REQUEST_URI'];
                if (!preg_match($path, $request_uri, $matches)) continue;
            } else {
                $request_uri = trim($_SERVER['REQUEST_URI'], '/');
                $path = trim($path, '/');
                if ($path != $request_uri) continue;
            }

            // проверяем подходит ли метод запроса
            if (!is_array($method)) $method = [$method];
            if (!in_array($_SERVER['REQUEST_METHOD'], $method))
                throw Fail::methodNotAccepted($_SERVER['REQUEST_METHOD']);

            // проверяем подходит ли роль пользователя
            /** @var array $roles */
            if (!in_array($role, $roles))
                throw Fail::accessDenied($role);

            // получаем входные данные в формате JSON, если они есть
            $data = json_decode(file_get_contents('php://input'), true);

            // проверяем другие источники
            if (!$data) {
                foreach ([$_POST, $_GET] as $source) {
                    if (!empty($source)) {
                        $data = $source;
                        break;
                    }
                }
            }

            // достаем переменные из route, если есть (только строковые ключи)
            if (isset($matches)) {
                !$data && $data = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key) && !array_key_exists($key, $data)) $data[$key] = $value;
                }
            }

            // Запускаем контроллер
            (new $controller)->$action($id, $data);

            return;
        }

        throw Fail::routeNotFound();
    }
}
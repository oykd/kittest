<?php

namespace Models;

use \Exceptions\AdminException as Fail;
use \DB\MySQL as DB;

/**
 * Class Admin
 * @package Models
 */
class Admin extends BaseModel
{
    // используем паттерн Singleton
    use \Singleton;

    /** Таблица MySQL */
    const table = 'admins';

    /** Маска для логина */
    const Mask = '/^[A-Z|a-z|@|_|\-|0-9|.|)|(|\]|\[]+$/';

    /** Минимальная длина логина */
    const MinLoginLength = 5;

    /** Минимальная длина пароля */
    const MinPasswordLength = 5;

    protected $name = null;

    /**
     * Синоним getInstance
     *
     * @return self
     */
    public static function get()
    {
        /** @var Admin $instance */
        $instance = self::getInstance();
        return $instance;
    }

    /**
     * Регистрируем нового админа
     *
     * @param string $login
     * @param string $password
     * @param bool $autologin
     * @throws \Exceptions\DBException | \Exceptions\AdminException
     */
    public function register($login, $password, $autologin = false)
    {
        // Проверки вводных данных
        if (strlen($login) < self::MinLoginLength)
            throw Fail::loginToShort($login);
        if (!preg_match(self::Mask, $login))
            throw Fail::loginHasIncorrectSymbols($login);
        if (strlen($password) < self::MinPasswordLength)
            throw Fail::passwordToShort(strlen($password), self::MinPasswordLength);
        if (DB::query("SELECT * FROM `" . self::table . "` WHERE `login` = ?", $login)->first())
            throw Fail::loginAlreadyExist($login);

        // Сохраняем новую учетную запись
        DB::query(
            "INSERT INTO `" . self::table . "` (`login`, `password`) VALUES (?, ?)",
            [$login, password_hash($password, PASSWORD_BCRYPT)]
        );

        // Автологин
        $autologin && $this->login($login, $password);
    }

    /**
     * Пробуем залогиниться
     *
     * @param string $login
     * @param string $password
     * @throws \Exceptions\DBException | \Exceptions\AdminException
     */
    public function login($login, $password)
    {
        // На всякий случай проверяем логин на некорректные символы
        if (strlen($login) < self::MinLoginLength)
            throw Fail::loginToShort($login);

        if (!preg_match(self::Mask, $login))
            throw Fail::loginHasIncorrectSymbols($login);

        // Ищем пользователя с таким логином
        if (!$data = DB::query("SELECT * FROM `" . self::table . "` WHERE `login` = ?", $login)->first())
            throw Fail::loginNotFound($login);

        // Сверяем пароли
        if (!password_verify($password, $data['password']))
            throw Fail::incorrectPassword();

        // Генерируем и сохраняем новую сессию
        $session = $this->session();
        $_SESSION['login_session'] = $session;
        DB::query("UPDATE `" . self::table . "` SET `session` = ? WHERE `login` = ?", [$session, $login]);
    }

    /**
     * Выходим
     */
    public function logout()
    {
        unset($_SESSION['login_session']);
    }

    /**
     * Проверяем залогинен ли юзер
     * Если залогинен возвращаем экземпляр класса, нет - null
     *
     * @return null|Admin
     * @throws \Exceptions\DBException | \Exceptions\AdminException
     */
    public static function init()
    {
        // инициализируем сессию
        if (session_status() == PHP_SESSION_NONE) {
            if (headers_sent()) throw Fail::headersAlreadySent();
            session_start();
        }

        // проверяем корректносить сессии
        if (!isset($_SESSION['login_session'])) return null;
        if ($data = DB::query("SELECT * FROM `" . self::table . "` WHERE `session` = ?", $_SESSION['login_session'])->first()) {
            /** @var Admin $instance */
            $instance = self::getInstance();
            $instance->name = $data['login'];
            return $instance;
        }

        return null;
    }

    /**
     * Генерируем кодовую строку для сессии
     *
     * @param int $length
     * @param bool $withMicroTimePrefix
     * @return string
     */
    protected function session($length = 32, $withMicroTimePrefix = true)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $R = '';
        for ($i = 0; $i < $length; $i++)
            $R .= $characters[rand(0, $charactersLength - 1)];
        return $withMicroTimePrefix ? (microtime(true) * 10000) . '.' . $R : $R;
    }

    /**
     * Получаем имя пользователя
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
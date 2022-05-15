<?php

namespace DB;

use \Exceptions\DBException as Fail;

/**
 * Class MySQL
 *
 * @package DB
 */
final class MySQL implements DBInterface
{
    // Используем шаблон Singleton
    use \Singleton;

    /** Обычные настройки, которые часто совпадают */
    const defaultSettings = [
        'host' => 'localhost',
        'port' => 3306,
        'charset' => 'utf8',
        'socket' => null,
        'flags' => null,
    ];

    /** @var MySQL */
    protected static $instance;

    /** @var \mysqli */
    protected $link;

    /** @var \mysqli_result */
    protected $query;

    /** @var string */
    protected $table;

    /** @var int */
    protected $affectedRows = 0;

    /**
     * Синоним getInstance
     *
     * @return self
     */
    public static function get()
    {
        /** @var MySQL $instance */
        $instance = self::getInstance();
        return $instance;
    }

    /**
     * destructor
     */
    public function __destruct()
    {
        $this->link->close();
        $this->link = null;
    }

    /**
     * Создаем соединение к БД
     *
     * @param array $settings
     * @return self
     * @throws \Exceptions\DBException
     */
    public static function connect($settings)
    {
        $instance = self::getInstance();

        $instance->link = mysqli_init();

        // Устанавливаем значения настроек, которых возможно не хватает
        foreach (self::defaultSettings as $key => $value) {
            if (!array_key_exists($key, $settings)) $settings[$key] = $value;
        }

        // Дополнительные настройки
        // Например: MYSQLI_OPT_SSL_VERIFY_SERVER_CERT => true
        if (isset($settings['options'])) {
            foreach ($settings['options'] as $option => $value) {
                $instance->link->options($option, $value);
            }
        }

        // Если БД находится удаленно и требует подключение через SSL
        if (isset($settings['ssl'])) {

            // проверяем наличие необходимых значений
            foreach (['ssl_client_key', 'ssl_client_cert', 'ssl_ca', 'ssl_capath', 'ssl_cipher'] as $field) {
                if (!array_key_exists($field, $settings['ssl'])) throw Fail::missingParameter($field);
            }

            // устанавливаем настройки SSL
            $instance->link->ssl_set(
                $settings['ssl']['ssl_client_key'],
                $settings['ssl']['ssl_client_cert'],
                $settings['ssl']['ssl_ca'],
                $settings['ssl']['ssl_capath'],
                $settings['ssl']['ssl_cipher']
            );
        }

        // проверяем наличие необходимых значений
        foreach (['host', 'user', 'password', 'database'] as $field) {
            if (!array_key_exists($field, $settings)) throw Fail::missingParameter($field);
        }

        // подключаемся
        $result = $instance->link->real_connect(
            $settings['host'],
            $settings['user'],
            $settings['password'],
            $settings['database'],
            $settings['port'],
            $settings['socket'],
            $settings['flags']
        );

        // не получилось?
        if (!$result) {
            throw Fail::connectError($instance->link->connect_errno, $instance->link->connect_error);
        }

        // устанавливаем нужную кодировку
        $instance->link->set_charset($settings['charset']);

        return self::get();
    }

    /**
     * Запрос к MySQL
     *
     * @param string $queryString
     * @param array $args
     * @return self
     * @throws \Exceptions\DBException
     */
    public static function query($queryString, $args = [])
    {
        /** @var MySQL $instance */
        $instance = self::getInstance();

        // если аргументов в запросе нет, подготавливать запрос нет необходимости
        if (empty($args)) {
            $instance->query = $instance->link->query($queryString);
            $instance->affectedRows = $instance->link->affected_rows;
            return $instance;
        }

        // подготавливаем запрос
        /** @var \mysqli_stmt $query */
        if (!$query = $instance->link->prepare($queryString)) {
            throw Fail::unableToPrepareStatement($queryString);
        }

        // привязываем аргументы
        if (!is_array($args)) $args = [$args];
        $types = '';
        $args_ref = [];
        foreach ($args as $k => &$arg) {
            $types .= self::getType($args[$k]);
            $args_ref[] = &$arg;
        }
        array_unshift($args_ref, $types);
        $query->bind_param(...$args_ref);

        // выполняем запрос
        if (!$query->execute()) {
            throw Fail::unableToProcessQuery($queryString);
        }

        // получаем результат
        $instance->query = $query->get_result();
        $instance->affectedRows = $query->affected_rows;

        return $instance;
    }

    /**
     * Получаем первую букву типа переменной
     *
     * @param mixed $var
     * @return string [s, d, i, b]
     */
    protected static function getType($var)
    {
        if (is_string($var)) return 's';
        if (is_float($var)) return 'd';
        if (is_int($var)) return 'i';
        return 'b';
    }

    /**
     * Получаем следующую строку результата или значение строки из определенного столбца
     *
     * @param null|string $key
     * @return array|mixed|false|null
     */
    public function next($key = null)
    {
        if (!is_object($this->query)) return false;

        $row = $this->query->fetch_array(MYSQLI_ASSOC); // MYSQLI_NUM, MYSQLI_BOTH

        // строки закончились?
        if (!is_array($row)) {
            $this->clear();
            return null;
        }

        // определенное значение
        if (isset($key)) {
            return array_key_exists($key, $row) ? $row[$key] : $row;
        }

        return $row;
    }

    /**
     * Закрываем текущий запрос
     */
    protected function clear()
    {
        if (!isset($this->query)) return;
        $this->query->close();
        $this->query = null;
    }

    /**
     * Получаем все найденные строки
     *
     * @param null|string $key
     * @return array
     */
    public function all($key = null)
    {
        $rows = [];
        while ($R = $this->next($key)) {
            $rows[] = $R;
        }
        return $rows;
    }

    /**
     * Синоним next
     *
     * @param null $key
     * @return mixed
     */
    public function first($key = null)
    {
        return $this->next($key);
    }

    /**
     * Получаем количество измененных строк из последнего запроса
     *
     * @return int
     */
    public function affected()
    {
        return $this->affectedRows;
    }

    /**
     * Проверяем существует ли таблица
     *
     * @param $table
     * @return bool
     * @throws \Exceptions\DBException
     */
    public function exists($table)
    {
        return !!self::query("SHOW TABLES LIKE '$table'")->first();
    }

    /**
     * Возвращаем последний вставленный id
     *
     * @return int|null
     */
    public function lastInsertId()
    {
        return MySqli_Insert_ID($this->link);
    }
}
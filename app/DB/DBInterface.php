<?php

namespace DB;

/**
 * Interface DBInterface
 * @package DB
 */
interface DBInterface
{
    public static function get();

    public static function connect($settings);

    public static function query($queryString, $args = []);

    public function next($key = null);

    public function first($key = null);

    public function all($key = null);

    public function exists($table);

    public function affected();

    public function lastInsertId();
}
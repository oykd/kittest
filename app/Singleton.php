<?php

/**
 * Trait Singleton
 */
trait Singleton
{
    /** @var object */
    protected static $instance;

    /**
     * Запрещаем unserialize()
     */
    private function __wakeup()
    {
    }

    /**
     * Запрещаем clone
     */
    private function __clone()
    {
    }

    /**
     * Запрещаем new
     */
    private function __construct()
    {
    }

    /**
     * Возвращаем всегда один и тот же экземпляр класса
     *
     * @return object
     */
    public static function getInstance()
    {
        return isset(self::$instance) ? self::$instance : self::$instance = new self;
    }
}
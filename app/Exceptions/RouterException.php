<?php

namespace Exceptions;

class RouterException extends \Exception
{
    /**
     * @return RouterException
     */
    public static function routeNotFound()
    {
        return new static("Page not found", 1);
    }

    /**
     * @param string $method
     * @return RouterException
     */
    public static function methodNotAccepted($method)
    {
        return new static("Method [$method] not accepted", 2);
    }

    /**
     * @param string $role
     * @return RouterException
     */
    public static function accessDenied($role)
    {
        return new static("Access denied for [$role]", 3);
    }
}
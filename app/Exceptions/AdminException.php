<?php

namespace Exceptions;

/**
 * Class AdminException
 * @package Exceptions
 */
class AdminException extends \Exception
{
    /**
     * @return AdminException
     */
    public static function headersAlreadySent()
    {
        return new static("Cant start session: headers already sent", 1);
    }

    /**
     * @param string $name
     * @return AdminException
     */
    public static function loginHasIncorrectSymbols($name)
    {
        return new static("Login [$name] has incorrect symbols", 2);
    }

    /**
     * @param string $name
     * @return AdminException
     */
    public static function loginToShort($name)
    {
        return new static("Login [$name] to short", 3);
    }

    /**
     * @param string $name
     * @return AdminException
     */
    public static function loginAlreadyExist($name)
    {
        return new static("Login [$name] already exist", 4);
    }

    /**
     * @param int $length
     * @param int $minLength
     * @return AdminException
     */
    public static function passwordToShort($length, $minLength)
    {
        return new static("Password length [$length] to short (Min: $minLength)", 5);
    }

    /**
     * @param string $name
     * @return AdminException
     */
    public static function loginNotFound($name)
    {
        return new static("Login [$name] not found", 6);
    }

    /**
     * @return AdminException
     */
    public static function incorrectPassword()
    {
        return new static("Incorrect password", 7);
    }

    /**
     * @return AdminException
     */
    public static function incorrectInputData()
    {
        return new static("Incorrect input data", 8);
    }

}
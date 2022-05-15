<?php
// DB.exception.php @ oykd-- 
// 13.05.2022 22:36

namespace Exceptions;

/**
 * Class DBException
 * @package Fail
 */
class DBException extends \Exception
{
    /**
     * @param string $name
     * @return DBException
     */
    public static function missingParameter($name)
    {
        return new static("Parameter [$name] is missing", 1);
    }

    /**
     * @param int $number
     * @param string $description
     * @return DBException
     */
    public static function connectError($number, $description)
    {
        return new static("Connect error ($number): $description", 2);
    }

    /**
     * @param string $queryString
     * @return DBException
     */
    public static function unableToPrepareStatement($queryString)
    {
        return new static("Unable to prepare: [$queryString]", 3);
    }

    /**
     * @param string $queryString
     * @return DBException
     */
    public static function unableToProcessQuery($queryString)
    {
        return new static("Unable to process: [$queryString]", 4);
    }

}
<?php

namespace Exceptions;

class TreeException extends \Exception
{
    /**
     * @return TreeException
     */
    public static function incorrectParameters()
    {
        return new static("Incorrect input parameters", 1);
    }

    /**
     * @param int $id
     * @return TreeException
     */
    public static function parentIdNotFound($id)
    {
        return new static("Parent id [$id] not found", 2);
    }

    /**
     * @param int $min
     * @return TreeException
     */
    public static function nameToShort($min)
    {
        return new static("Name to short (min: $min)", 3);
    }

    /**
     * @param int $max
     * @return TreeException
     */
    public static function nameToLong($max)
    {
        return new static("Name to long (max: $max)", 4);
    }

    /**
     * @param int $id
     * @return TreeException
     */
    public static function idNotFound($id)
    {
        return new static("Id [$id] not found", 5);
    }
}
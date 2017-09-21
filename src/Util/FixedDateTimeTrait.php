<?php

namespace TestTools\Util;

use DateTime;
use InvalidArgumentException;

/**
 * You can use this trait to easily make any existing class work with FixedDateTime instances
 *
 * @author Michael Mayer <michael@lastzero.net>
 * @package TestTools
 * @license MIT
 */
trait FixedDateTimeTrait
{
    /**
     * DateTime class name (can be changed for testing)
     *
     * @var string
     */
    private static $_dateTimeClassName = '\DateTime';

    /**
     * Returns the current DateTime class name (can be changed for testing)
     *
     * @param string $className
     * @return string
     */

    public static function setDateTimeClassName(string $className)
    {
        if (!class_exists($className)) {
            throw new InvalidArgumentException($className . ' does not exist');
        }

        self::$_dateTimeClassName = $className;
    }

    /**
     * Returns a new DateTime instance
     *
     * @param string $time
     * @param \DateTimeZone|NULL $timezone
     * @return DateTime
     */
    protected function getDateTimeInstance(string $time = "now", \DateTimeZone $timezone = null): DateTime
    {
        $result = new self::$_dateTimeClassName($time, $timezone);

        return $result;
    }
}
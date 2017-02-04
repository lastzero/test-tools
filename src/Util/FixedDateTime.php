<?php

namespace TestTools\Util;

/**
 * FixedDateTime class as drop-in replacement for the original DateTime
 *
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class FixedDateTime extends \DateTime
{
    static $now = '2016-01-22T23:42:05.012345+00:00';

    public function __construct($time = 'now', \DateTimeZone $timezone = null)
    {
        if ($time == 'now') {
            $time = self::$now;
        }

        parent::__construct($time, $timezone);
    }
}
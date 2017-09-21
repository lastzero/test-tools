<?php

namespace TestTools\Tests\Util;

use TestTools\Util\FixedDateTimeTrait;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @package TestTools
 * @license MIT
 */
class FixedDateTimeClass
{
    use FixedDateTimeTrait;

    public function getCurrentDateTime()
    {
        $result = $this->getDateTimeInstance('now');

        return $result;
    }
}
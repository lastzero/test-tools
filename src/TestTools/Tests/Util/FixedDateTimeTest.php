<?php

namespace TestTools\Tests\Util;

use TestTools\Util\FixedDateTime;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @package TestTools
 * @license MIT
 */
class FixedDateTimeTest extends TestCase
{
    public function testConstructor()
    {
        $result = new FixedDateTime();
        $expected = '2016-01-22T23:42:05+00:00';

        $this->assertEquals($expected, $result->format('c'));
        $this->assertEquals('012345', $result->format('u'));
    }
}
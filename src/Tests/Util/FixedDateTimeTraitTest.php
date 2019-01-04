<?php

namespace TestTools\Tests\Util;

use PHPUnit\Framework\TestCase;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @package TestTools
 * @license MIT
 */
class FixedDateTimeTraitTest extends TestCase
{
    public function testGetCurrentDateTime()
    {
        FixedDateTimeClass::setDateTimeClassName('\DateTime');

        $now = new \DateTime();

        $instance = new FixedDateTimeClass();

        $result = $instance->getCurrentDateTime();

        $this->assertLessThan(60, $result->diff($now)->s);
    }

    public function testGetFixedDateTime()
    {
        FixedDateTimeClass::setDateTimeClassName('\TestTools\Util\FixedDateTime');

        $instance = new FixedDateTimeClass();

        $result = $instance->getCurrentDateTime();
        $expected = '2016-01-22T23:42:05+00:00';

        $this->assertEquals($expected, $result->format('c'));
        $this->assertEquals('012345', $result->format('u'));
    }
}
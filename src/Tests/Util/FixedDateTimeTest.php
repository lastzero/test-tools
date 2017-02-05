<?php

declare(strict_types=1);

namespace TestTools\Tests\Util;

use PHPUnit\Framework\TestCase;
use TestTools\Util\FixedDateTime;

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
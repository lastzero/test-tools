<?php

namespace TestTools\Tests\Profiler;

use TestTools\Profiler\Profiler;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package TestTools
 * @license MIT
 */
class ProfilerTest extends TestCase
{
    public function setUp()
    {
        Profiler::clear();
    }

    public function testAddStep () {
        Profiler::start();

        usleep(20000);

        Profiler::addStep('Foo');

        usleep(50000);

        Profiler::addSilentStep('Bar');

        usleep(12000);

        Profiler::stop();

        $result = Profiler::getResultAsTable();

        $this->assertContains('Foo', $result);
        $this->assertContains('Start', $result);
        $this->assertContains('Done', $result);
        $this->assertNotContains('Bar', $result);
    }

    public function testAggregate () {
        Profiler::start('Start Aggregate');

        for($i = 0; $i < 23; $i++) {
            Profiler::startAggregate('Foo', 'Bar');
            usleep(5000);
            Profiler::stopAggregate('Foo', 'Bar');
        }

        for($i = 0; $i < 19; $i++) {
            Profiler::startAggregate('Foo', 'Baz');
            usleep(2000);
            Profiler::stopAggregate('Foo', 'Baz');
        }

        Profiler::stop();

        $result = Profiler::getResultAsTable();

        $this->assertContains('Foo', $result); // Aggregation label
        $this->assertContains('Bar', $result);
        $this->assertContains('Baz', $result);
        $this->assertContains('42', $result); // Aggregated count
        $this->assertContains('23', $result);
        $this->assertContains('19', $result);
    }
}
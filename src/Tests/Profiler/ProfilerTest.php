<?php

namespace TestTools\Tests\Profiler;

use TestTools\Profiler\Profiler;
use PHPUnit\Framework\TestCase;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @package TestTools
 * @license MIT
 */
class ProfilerTest extends TestCase
{
    public function setUp(): void
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

        $this->assertStringContainsString('Foo', $result);
        $this->assertStringContainsString('Start', $result);
        $this->assertStringContainsString('Done', $result);
        $this->assertStringNotContainsString('Bar', $result);
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

        $this->assertStringContainsString('Foo', $result); // Aggregation label
        $this->assertStringContainsString('Bar', $result);
        $this->assertStringContainsString('Baz', $result);
        $this->assertStringContainsString('42', $result); // Aggregated count
        $this->assertStringContainsString('23', $result);
        $this->assertStringContainsString('19', $result);
    }
}
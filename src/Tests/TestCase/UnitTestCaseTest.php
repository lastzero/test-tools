<?php

namespace TestTools\Tests\Buzz;

use TestTools\TestCase\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @package TestTools
 * @license MIT
 */
class UnitTestCaseTest extends UnitTestCase
{
    public function testGetFixturePath()
    {
        $fixturePath = $this->getContainer()->getParameter('fixture.path') . '/';
        $this->assertStringEndsWith('src/Tests/_fixtures/TestCase/UnitTestCase/', $fixturePath);

        $dbal = $this->get('dbal.connection');
        $this->assertInstanceOf('TestTools\Doctrine\DBAL\Connection', $dbal);
        $this->assertEquals($fixturePath, $dbal->getFixturePath());
    }

    public function testContainerWithoutCloning()
    {
        $container = new ContainerBuilder();

        $locator = new FileLocator(__DIR__);
        $loader = new YamlFileLoader($container, $locator);
        $loader->load('container.yml');

        $container->setParameter('fixture.path', __DIR__ . '/foo/');
        $container->setParameter('base.path', __DIR__);

        $this->assertEquals(__DIR__ . '/foo/', $container->getParameter('fixture.path'));
        $this->assertEquals(__DIR__, $container->getParameter('base.path'));

        $buzz = $container->get('dbal.connection');
        $this->assertInstanceOf('TestTools\Doctrine\DBAL\Connection', $buzz);
        $this->assertEquals($container->getParameter('fixture.path'), $buzz->getFixturePath());

        $container->reset();

        $container->setParameter('fixture.path', __DIR__ . '/bar/');
        $container->setParameter('base.path', __DIR__);

        $this->assertEquals(__DIR__ . '/bar/', $container->getParameter('fixture.path'));
        $this->assertEquals(__DIR__, $container->getParameter('base.path'));

        $buzz = $container->get('dbal.connection');
        $this->assertInstanceOf('TestTools\Doctrine\DBAL\Connection', $buzz);
        $this->assertEquals($container->getParameter('fixture.path'), $buzz->getFixturePath());
    }
}
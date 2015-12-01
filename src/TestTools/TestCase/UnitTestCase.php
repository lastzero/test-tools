<?php

namespace TestTools\TestCase;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * This base class for unit tests provides an integrated DI container
 * for more productive testing. The service instances are not cached 
 * between tests, but the container itself is which significantly improves test
 * performance (about 5 to 10 times faster).
 * 
 * USAGE: Simply create a config.yml (optionally config.local.yml for local
 * modifications) in your base test directory, for example
 * 
 *   Component/FooBarClient/Tests/config.yml
 * 
 * NOTE: The config parameter "fixture.path" is automatically set based on the 
 * test class filename and path to avoid conflicts/dependencies between 
 * different tests and enforce a consistent naming scheme.
 * 
 * IMPORTANT: Configuration details such as server URLs or login credentials
 * must be valid for development environments only, since all service / database
 * requests should be replaced by fixtures after the corresponding tests were
 * running for the first time. If a test fails on Jenkins/staging/integration
 * because of invalid URLs or credentials in config.yml, you must make sure that 
 * all code that accesses external resources is using fixtures (or mock objects)
 * and that all fixtures are checked in properly.
 * 
 * You can use TextTools\Fixture\FileFixture to easily make your
 * existing classes work with file based fixtures:
 * 
 *   http://martinfowler.com/bliki/SelfInitializingFake.html
 * 
 * Documentation of the Symfony dependency injection component:
 * 
 *   http://symfony.com/doc/current/components/dependency_injection/introduction.html
 *
 * @author Michael Mayer <michael@lastzero.net>
 * @package TestTools
 * @license MIT
 */
abstract class UnitTestCase extends \PHPUnit_Framework_TestCase
{
    use FixturePathTrait;
    private static $containers = array();
    private $container;
    private $loader;

    protected function setContainer(TestContainerBuilder $container)
    {
        $this->container = $container;
    }
    
    protected function configureContainer () {
        $this->container->setParameter('fixture.path', $this->getFixturePath());
        $this->container->setParameter('base.path', $this->getBasePath());
    }
    
    protected function getContainer() 
    {
        if(isset(self::$containers[$this->getTestBasePath()])) {
            // Use cached container (creating a new container from yml is too slow)
            $this->setContainer(clone self::$containers[$this->getTestBasePath()]);
            
            $this->configureContainer();
            
            $this->container->clearInstances();
        } elseif(!$this->container) {
            // Create new container
            $this->setContainer(new TestContainerBuilder());
            
            $this->configureContainer();
            
            $locator = new FileLocator($this->getTestBasePath());
            $this->loader = new YamlFileLoader($this->container, $locator);
            
            $this->loader->load('config.yml');
            
            try {
                $this->loader->load('config.local.yml');
            } catch(\InvalidArgumentException $e) {
                // No local config found
            }
            
            // Clone container to static cache
            self::$containers[$this->getTestBasePath()] = clone $this->container;
        }
        
        return $this->container;
    }
    
    protected function get($service) 
    {
        return $this->getContainer()->get($service);
    }
}
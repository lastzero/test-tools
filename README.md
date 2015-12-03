Test Tools for PHPUnit
======================

[![Build Status](https://travis-ci.org/lastzero/test-tools.png?branch=master)](https://travis-ci.org/lastzero/test-tools)
[![Latest Stable Version](https://poser.pugx.org/lastzero/test-tools/v/stable.svg)](https://packagist.org/packages/lastzero/test-tools)
[![Total Downloads](https://poser.pugx.org/lastzero/test-tools/downloads.svg)](https://packagist.org/packages/lastzero/test-tools)
[![License](https://poser.pugx.org/lastzero/test-tools/license.svg)](https://packagist.org/packages/lastzero/test-tools)

**The goal of this project is to increase testing productivity by leveraging the power of dependency injection and self-initializing fixtures for PHPUnit tests.**

* **UnitTestCase** extends `PHPUnit_Framework_TestCase` with a configurable dependency injection container (Symfony Components)
* **WebTestCase** and **CommandTestCase** extend UnitTestCase for functional testing of Symfony2 Web and CLI applications
* **FileFixture** reads and writes serialized data from/to the file system
* **SelfInitializingFixtureTrait** and **BlackBox** add fixture support to almost any database or service client (record and playback)
* To cover some of the most common use cases, **Doctrine DBAL** (SQL), **Guzzle** and **Buzz** (HTTP) are supported out of the box

Dependency Injection
--------------------

Here’s an example of a test case built with `TestTools\TestCase\UnitTestCase` – note the **setUp()** method, which get’s the ready-to-use object from the dependency injection container:

    use TestTools\TestCase\UnitTestCase;

    class FooTest extends UnitTestCase
    {
        protected $foo;

        public function setUp()
        {
            $this->foo = $this->get('foo');
        }

        public function testBar()
        {
            $result = $this->foo->bar('Pi', 2);
            $this->assertEquals(3.14, $result);
        }
    }

To define services, simply create a `config.yml` (optionally `config.local.yml` for local modifications) in your base test directory, for example

    Bundle/Example/Tests/config.yml
    
The YAML file must contain the sections "parameters" and "services". If you're not yet familiar with dependency injection or the config file format, please read the documentation on symfony.com (it's really simple):

http://symfony.com/doc/current/components/dependency_injection/introduction.html

The Symfony Components DI container was chosen, because of it's easy to understand container configuration in YAML.

Since global state must be avoided while performing tests, the service instances are not cached between tests. The service definitions in the container are reused however. This significantly improves test performance compared to a full container reinitialization before each test (about 5 to 10 times faster).

TestTools can be used to test **any application**, framework or library, just like `PHPUnit_Framework_TestCase`.

Classic vs mockist style of unit testing
----------------------------------------

These tools **simplify using real objects and test doubles via dependency injection**, so some developers might criticise that the resulting tests are not *true* unit tests as **class dependencies are not mocked by default**. Mocking is creating objects that simulate the behaviour of real objects. Martin Fowler refers to this as the **classic and mockist styles of unit testing**:

"The **classical TDD style** is to use **real objects** if possible and a double if it's awkward to use the real thing. So a classical TDDer would use a real warehouse and a double for the mail service. The kind of double doesn't really matter that much.

A **mockist TDD** practitioner, however, will always use a mock for any object with interesting behavior. In this case for both the warehouse and the mail service." -- [Martin Fowler](http://martinfowler.com/articles/mocksArentStubs.html)

[Mocks and test doubles](http://martinfowler.com/bliki/TestDouble.html) are required to be able to test sometimes, but since **mocking can be a time-consuming endeavour**, you should try to avoid their widespread usage and prefer using real objects instead. They do no harm - quite the contrary: You can instantly see, how the real objects interact with each other instead of waiting for functional tests. Actually, the need for excessive mocking is an indicator for bad software design.

In theory, the mockist style can be a bit **more precise** when it comes to finding a broken line of code, because all classes are tested in complete isolation. In practice, **classic unit tests will also provide you with a stack trace** that points you to the right line of code:

"We didn't find it difficult to track down the actual fault, even if it caused neighboring tests to fail. **So we felt isolation wasn't an issue in practice**." -- [Martin Fowler](http://martinfowler.com/bliki/UnitTest.html)

In the worst case, more than one test case fails, if just one class or function is broken – will give you even more information about the issue and allows to find and fix affected code easily.

Even code that depends on databases or Web services, can be easily tested using **self-initializing fixtures** instead of hand-written mocks. The only thing they can not properly simulate is state, but robust unit tests shouldn't depend on state anyways. If you want to test state, use [functional tests** of the user interface or API](http://martinfowler.com/bliki/TestPyramid.html) instead.

Self-initializing Fixtures
--------------------------

The basic concept of self-initializing fixtures is described by [Martin Fowler](http://martinfowler.com/bliki/SelfInitializingFake.html) and can be applied to all types of external data stores (databases) and services (SOAP/REST):

`TestTools\Fixture\SelfInitializingFixtureTrait` enables existing classes to work with file based fixtures (record and playback):

    use TestTools\Fixture\SelfInitializingFixtureTrait;

    class Foo extends SomeBaseClass
    {
        use SelfInitializingFixtureTrait;

        public function bar($name, $type, array $baz = array())
        {
            return $this->callWithFixtures('bar', func_get_args());
        }
    }

The Doctrine connection class (`TestTools\Doctrine\DBAL\Connection`) serves as a ready-to-use example. It works as a wrapper for the standard connection class (white box inheritance). Black box inheritance (`TestTools\Fixture\BlackBox`) is used by the Buzz client (`TestTools\Buzz\Client`) to encapsulate any ClientInterface.

`TestTools\TestCase\WebTestCase.php` can be used for functional testing of Symfony controllers based on the 
regular DI configuration of your application:

    use TestTools\TestCase\WebTestCase;
    use Symfony\Component\DependencyInjection\ContainerInterface;

    class FooControllerTest extends WebTestCase
    {
        protected function configureFixtures(ContainerInterface $container)
        {
            // Service instance must provide a useFixtures() method for this to work
            $container->get('db')->useFixtures($this->getFixturePath());
        }

        public function testGetBar()
        {
            $this->client->getRequest('/foo/bar/Pi', array('precision' => 2));
            $response = $this->client->getResponse();
            $this->assertEquals(3.14, $response->getContent());
        }
    }

DI container configuration for self-initializing fixtures
---------------------------------------------------------
A config parameter "fixture.path" (for storing file based fixtures) is automatically set based on the test class filename and path to avoid conflicts/dependencies between different tests and enforce a consistent naming scheme. The directory is created automatically.

Example container configuration (`TestTools/Tests/config.yml`):
```
parameters:
    dbal.params:
        driver:         mysqli
        host:           localhost
        port:           3306
        dbname:         testtools
        charset:        utf8
        user:           testtools
        password:       testtools
        
services:
    dbal.driver:
        class: Doctrine\DBAL\Driver\Mysqli\Driver

    dbal.connection:
        class: TestTools\Doctrine\DBAL\Connection
        arguments:
            - %dbal.params%
            - "@dbal.driver"
        calls:
            - [setFixturePrefix, ['sql']]
            - [useFixtures, ["%fixture.path%"]]

    buzz.client:
        class: Buzz\Client\FileGetContents

    buzz.fixture:
        class: TestTools\Buzz\Client
        arguments:
            - "@buzz.client"
        calls:
            - [useFixtures, ["%fixture.path%"]]
```

When using a dependency injection container in conjunction with fixtures, you don't need to care about different environments such as development and production:
Configuration details (e.g. login credentials) must be valid for development environments only, since service / database requests should be replaced by fixtures from the file system after the  corresponding tests were running for the first time. If a test fails on Jenkins or Travis CI because of invalid URLs or credentials in config.yml, you must make sure that all code that  accesses external resources is using fixtures (or mock objects) and that all fixtures are checked in properly.

Composer
--------

If you are using composer, simply add "lastzero/test-tools" to your composer.json file and run `composer update`:

    "require-dev": {
        "lastzero/test-tools": "~2.0"
    }

For PHP 5.4 compatibility, use version "~1.2".

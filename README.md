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

`TestTools\TestCase\UnitTestCase` contains an integrated Symfony2 DI container for more productive testing:

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

*Note: UnitTestCase can be used to test any application, framework or library, just like PHPUnit_Framework_TestCase. It is not limited to the Symfony2 ecosystem. The Symfony2 DI container was primarily chosen, because of it's easy to understand container configuration in YAML.*

To define services, simply create a `config.yml` (optionally `config.local.yml` for local modifications) in your base test directory, for example

    Bundle/Example/Tests/config.yml
    
The YAML file must contain the sections "parameters" and "services". If you're not yet familiar with dependency injection or the config file format, please read the documentation on symfony.com (it's really simple):

http://symfony.com/doc/current/components/dependency_injection/introduction.html

Since global state must be avoided while performing tests, the service instances are not cached between tests. The service definitions in the container are reused however. This significantly improves test performance compared to a full container reinitialization before each test (about 5 to 10 times faster).

Self-initializing Fixtures
--------------------------
The basic concept of self initializing fixtures is described by Martin Fowler and can be applied to all
types of external data stores (databases) and services (SOAP/REST):

http://martinfowler.com/bliki/SelfInitializingFake.html 
 
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
            - @dbal.driver
        calls:
            - [setFixturePrefix, ['sql']]
            - [useFixtures, ["%fixture.path%"]]

    buzz.client:
        class: Buzz\Client\FileGetContents

    buzz.fixture:
        class: TestTools\Buzz\Client
        arguments:
            - @buzz.client
        calls:
            - [useFixtures, ["%fixture.path%"]]
```

When using a dependency injection container in conjunction with fixtures, you don't need to care about different environments such as development and production:
Configuration details (e.g. login credentials) must be valid for development environments only, since service / database requests should be replaced by fixtures from the file system after the  corresponding tests were running for the first time. If a test fails on Jenkins or Travis CI because of invalid URLs or credentials in config.yml, you must make sure that all code that  accesses external resources is using fixtures (or mock objects) and that all fixtures are checked in properly.

Composer
--------

If you are using composer, just add "lastzero/test-tools" to your composer.json file:

    "require-dev": {
        "lastzero/test-tools": "*"
    }

Excessive mocking is an anti-pattern and hurts your productivity
----------------------------------------------------------------

Some developers might criticise that the resulting tests are just component tests and not true unit tests. Simply put, component tests are like unit tests except that you don't mock class dependencies by default but use real objects (ideally via dependency injection). Mocking is creating objects that simulate the behaviour of real objects.

There's an awesome explanation why mocks are evil by Stanislav Bashkyrtsev (http://qala.io/blog/test-pyramid.html):

"Mocks interact with the internal logic of your classes and therefore will change every time that logic changes. Also, we often find ourselves in situations when we mock a lot and therefore we mostly test how we initialize our mocks rather than how our business logic works. So mocks are evil, but sometimes are necessary. Component Tests will free us from unnecessary mocking."

Mocks are required to be able to test sometimes, but since mocking can be a costly endeavour, you should try to avoid their widespread usage and prefer component tests instead. They do no harm - quite the contrary: You can instantly see, how the real objects interact with each other instead of waiting for functional tests.

In theory, true unit tests can be more precise when it comes to finding a broken line of code, because all classes are tested in complete isolation. In practice, component tests will provide you with a stack trace that points you to the right line of code. In the worst case, more than one test case fails, if just one class or function is broken. Of course, you should still create test cases for all your classes.

Even code that depends on databases or Web services, can be easily tested using self-initializing fixtures instead of hand-written mocks. The only thing they can not properly simulate is state, but robust unit tests shouldn't depend on state anyways. If you want to test state, use functional tests instead.



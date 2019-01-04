TestTools for PHPUnit
=====================

[![License](https://poser.pugx.org/lastzero/test-tools/license.svg)](https://packagist.org/packages/lastzero/test-tools)
[![Latest Stable Version](https://poser.pugx.org/lastzero/test-tools/v/stable.svg)](https://packagist.org/packages/lastzero/test-tools)
[![Total Downloads](https://poser.pugx.org/lastzero/test-tools/downloads.svg)](https://packagist.org/packages/lastzero/test-tools)
[![Test Coverage](https://codecov.io/gh/lastzero/test-tools/branch/master/graph/badge.svg)](https://codecov.io/gh/lastzero/test-tools)
[![Build Status](https://travis-ci.org/lastzero/test-tools.png?branch=master)](https://travis-ci.org/lastzero/test-tools)

**This library improves testing productivity by adding a configurable service container and self-initializing fakes to PHPUnit.**

* **UnitTestCase** adds the Symfony [service container](http://symfony.com/doc/current/service_container.html) to `PHPUnit\Framework\TestCase` (configuration via `config.yml` file in the Tests directory)
* **WebTestCase** and **CommandTestCase** extend UnitTestCase for functional testing of Symfony Web and CLI applications
* **FileFixture** reads and writes serialized data from/to the file system
* **SelfInitializingFixtureTrait** and **BlackBox** add fixture support to almost any database or service client (record and playback) to provide self-initializing fakes as test doubles
* **Doctrine DBAL** is supported out of the box

Service Container
-----------------

Here’s an example of a test case built with `TestTools\TestCase\UnitTestCase` – note the **setUp()** method, which get’s the ready-to-use object from the dependency injection container:

```php
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
```

To define services, simply create a `config.yml` (optionally `config.local.yml` for local modifications) in your base test directory, for example

    Bundle/Example/Tests/config.yml
    
The YAML file must contain the sections "parameters" and "services". If you're not yet familiar with dependency injection or the config file format, please read the documentation on symfony.com (it's really simple):

http://symfony.com/doc/current/components/dependency_injection/introduction.html

The Symfony service container was chosen, because of it's easy to understand container configuration in YAML.

Since global state must be avoided while performing tests, the service instances are not cached between tests. The service definitions in the container are reused however. This significantly improves test performance compared to a full container reinitialization before each test (about 5 to 10 times faster).

TestTools can be used to test any application, framework or library, just like `PHPUnit\Framework\TestCase`.

Classic vs Mockist Style of Unit Testing
----------------------------------------

These tools **simplify writing unit tests using real objects and test doubles** via dependency injection, so some developers might be concerned that the resulting tests are not *true* unit tests as **class dependencies are not mocked by default**. Mocking is creating objects that simulate the behaviour of real objects. These apparently conflicting approaches are referred to as the **classic and mockist styles of unit testing**:

"The **classical TDD style** is to use **real objects** if possible and a double if it's awkward to use the real thing. So a classical TDDer would use a real warehouse and a double for the mail service. The kind of double doesn't really matter that much.

A **mockist TDD** practitioner, however, will always use a mock for any object with interesting behavior. In this case for both the warehouse and the mail service." -- [Martin Fowler](http://martinfowler.com/articles/mocksArentStubs.html)

[Mocks and test doubles](http://martinfowler.com/bliki/TestDouble.html) are required to be able to test sometimes, but creating and maintaining mocks can be a boring, time-consuming endeavour. Therefore, you should think about avoiding their widespread usage and prefer using real objects instead. From my experience, they do no harm – quite the contrary: You can instantly see, how the real objects interact with each other instead of waiting for functional tests. Actually, the need for excessive mocking is an indicator for bad software design.

In theory, the mockist style can be a bit **more precise** when it comes to finding a broken line of code, because all classes are tested in complete isolation. In practice, **classic unit tests will also provide you with a stack trace** that points you to the right line of code:

"We didn't find it difficult to track down the actual fault, even if it caused neighboring tests to fail. **So we felt isolation wasn't an issue in practice**." -- [Martin Fowler](http://martinfowler.com/bliki/UnitTest.html)

In the worst case, more than one test case fails, if just one class or function is broken – this will give you even more information about the issue and allows to find and fix affected code easily.

Even code that depends on databases or Web services, can be easily tested using **self-initializing fixtures** instead of hand-written mocks. The only thing they can not properly simulate is state, but robust unit tests shouldn't depend on state anyways. If you want to test state, use [functional tests of the user interface or API](http://martinfowler.com/bliki/TestPyramid.html) instead.

Self-initializing Fakes
-----------------------

The concept of [self-initializing fakes](http://martinfowler.com/bliki/SelfInitializingFake.html) as test doubles can be applied to all types of external data stores (databases) and services like SOAP or REST APIs.

`SelfInitializingFixtureTrait` enables existing classes to work with file based fixtures (record and playback):

```php
use TestTools\Fixture\SelfInitializingFixtureTrait;

class Foo extends SomeBaseClass
{
    use SelfInitializingFixtureTrait;

    public function bar($name, $type, array $baz = array())
    {
        return $this->callWithFixtures('bar', func_get_args());
    }
}
```

The Doctrine connection class (`TestTools\Doctrine\DBAL\Connection`) serves as a ready-to-use example. It works as a wrapper for the standard connection class (white box inheritance). Black box inheritance (`TestTools\Fixture\BlackBox`) can be used to encapsulate any client interface.

`TestTools\TestCase\WebTestCase.php` can be used for functional testing of Symfony controllers based on the 
regular service configuration of your application:

```php
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
        $response = $this->getRequest('/foo/bar/Pi', array('precision' => 2));
        $this->assertEquals(3.14, $response->getContent());
    }
}
```

Service container configuration for self-initializing fakes
-----------------------------------------------------------
A config parameter "fixture.path" (for storing file based fixtures as fakes) is automatically set based on the test class filename and path to avoid conflicts/dependencies between different tests and enforce a consistent naming scheme. The directory is created automatically. The parameter "base.path" is also available (points to the parent directory of "Tests").

Example container configuration (`TestTools/Tests/config.yml`):

```yaml
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
```

When using a service container in conjunction with fixtures, you don't need to care about different environments such as development and production:
Configuration details (e.g. login credentials) must be valid for development environments only, since service / database requests should be replaced by fixtures from the file system after the  corresponding tests were running for the first time. If a test fails on Jenkins or Travis CI because of invalid URLs or credentials in config.yml, you must make sure that all code that  accesses external resources is using fixtures (or mock objects) and that all fixtures are checked in properly.

Composer
--------

If you are using composer, simply run:

```bash
composer require --dev lastzero/test-tools 
```

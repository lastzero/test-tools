Test Tools for PHP
==================

[![Build Status](https://travis-ci.org/lastzero/test-tools.png?branch=master)](https://travis-ci.org/lastzero/test-tools)

The test tools library provides the following components:

* A basic **FileFixture** class to read and write serialized data from/to the file system
* **SelfInitializingFixtureTrait** and **BlackBox** to add fixture support to almost any database or service client (record and playback)
* Ready-to-use self-initializing fixture classes for **Doctrine DBAL** (SQL) and **Buzz** (HTTP)
* **UnitTestCase** with integrated dependency injection container based on Symfony Components and PHPUnit
* **WebTestCase** and **CommandTestCase** for functional testing of Symfony applications

TestTools\TestCase\UnitTestCase.php contains an integrated DI container for more productive testing:

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

Simply create a config.yml (optionally config.local.yml for local modifications) in your base test directory,
for example

    Bundle/Example/Tests/config.yml
    
The yaml file must contain the sections "parameters" and "services". If you're not yet familiar with
dependency injection or the config file format, you can find more information on symfony.com:

http://symfony.com/doc/current/components/dependency_injection/introduction.html

Since global state must be avoided while performing tests, the service instances are not 
cached between tests. The service definitions in the container are reused however. This significantly
improves test performance compared to a full container reinitialization before each test (about 5 to 10 times faster).

**Note**: A config parameter "fixture.path" is automatically set based on the test class filename and path 
to avoid conflicts/dependencies between different tests and enforce a consistent naming scheme.
The directory is created automatically.

When using a dependency injection container in conjunction with fixtures, you don't need to care about 
different environments such as development and production:
Configuration details (e.g. login credentials) must be valid for development 
environments only, since service / database requests should be replaced by fixtures from the file system after the 
corresponding tests were running for the first time. If a test fails on Jenkins or Travis CI
because of invalid URLs or credentials in config.yml, you must make sure that all code that 
accesses external resources is using fixtures (or mock objects) and that all fixtures are checked in properly.
 
You can use TestTools\Fixture\SelfInitializingFixtureTrait.php to easily make any existing class
work with file based fixtures:

    use TestTools\Fixture\SelfInitializingFixtureTrait;

    class Foo extends SomeBaseClass
    {
        use SelfInitializingFixtureTrait;

        public function bar($name, $type, array $baz = array())
        {
            return $this->callWithFixtures('bar', func_get_args());
        }
    }

Have a look at the Doctrine fixture connection class (TestTools\Doctrine\DBAL\Connection.php) to see an
example. It works as a wrapper for the standard connection class (white box inheritance). The Buzz client
uses the BlackBox class to encapsulate any ClientInterface (black box inheritance)

The basic concept of self initializing fixtures is described by Martin Fowler and can be applied to all
types of external data stores (databases) and services (SOAP/REST):

http://martinfowler.com/bliki/SelfInitializingFake.html

TestTools\TestCase\WebTestCase.php can be used for functional testing of Symfony controllers based on the 
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

If you are using Composer, you just have to add "lastzero/test-tools" to your composer.json file:

    "require": {
        "lastzero/test-tools": ">=0.6"
    }

By making a donation, you can support the further development and maintenance of this library:

[![Flattr this git repo](http://api.flattr.com/button/flattr-badge-large.png)](https://flattr.com/submit/auto?user_id=lastzero&url=https://github.com/lastzero/test-tools&title=Test Tools for PHP&language=&tags=github&category=software)

Test Tools for PHP
==================

[![Build Status](https://travis-ci.org/lastzero/test-tools.png?branch=master)](https://travis-ci.org/lastzero/test-tools)

The test tools library provides the following components:

* A basic file fixture class to read and write data from/to the file system (no external dependencies)
* Automatic database fixtures for Doctrine DBAL
* Test dependency injection support built on top of the Symfony2 dependency injection container and PHPUnit

TestTools\TestCase\UnitTestCase.php contains an integrated DI container for more productive testing.
Simply create a config.yml (optionally config.local.yml for local modifications) in your base test directory,
for example

    Bundle/Example/Tests/config.yml
    
The yaml file must contain the sections "parameters" and "services". If you're not yet familiar with
dependency injection or the config file format, you can find more information in this documentation:

http://symfony.com/doc/current/components/dependency_injection/introduction.html

Since global state must be avoided while performing tests, the service instances are not 
cached between tests. The service definitions in the container are reused however. This significantly
improves test performance compared to a full container reinitialization before each test (about 5 to 10 times faster).

**Note**: A config parameter "fixture.path" is automatically set based on the test class filename and path 
to avoid conflicts/dependencies between different tests and enforce a consistent naming scheme.
The directory is created automatically.

When using a dependency injection container for tests, you should not need to care about different environments:
Configuration details such as server URLs or login credentials must be valid for development 
environments only, since service / database requests should be replaced by fixtures after the 
corresponding tests were running for the first time. If a test fails on Jenkins or Travis CI
because of invalid URLs or credentials in config.yml, you must make sure that all code that 
accesses external resources is using fixtures (or mock objects) and that all fixtures are checked in properly.
 
You can use TestTools\Fixture\FileFixture.php to easily make any existing classes work with file based fixtures.
Have a look at the doctrine fixture connection class (TestTools\Doctrine\FixtureConnection.php) to see an example.
The basic concept is described by Martin Fowler:
    
http://martinfowler.com/bliki/SelfInitializingFake.html

Composer can be used to add this library to your project:

    "require": {
        "lastzero/test-tools": ">=0.3"
    },

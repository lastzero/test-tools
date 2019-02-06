Changelog
=========

Release 5
---------

* 5.0.0 Upgraded to PHPUnit 8.0

Release 4
---------

* 4.2.1 Tested with PHP 7.2 & 7.3; Documentation clean-up

* 4.2.0 Added support for Symfony 4 and removed support for Buzz (API changed)

* 4.1.0 Added FixedDateTimeTrait

* 4.0.0 Version 4 uses new fixture filenames to better support case insenstive file systems. 
        Please delete and re-create all existing fixtures.

Release 3
---------

* 3.0.2 Removed deprecated Guzzle 3.9.3 support

* 3.0.1 Merged pull requests; improved composer.json and documentation

* 3.0.0 PHP 7, PSR-4 and PHPUnit 6 compatibility

Release 2
---------

* 2.1.0 Marking release as compatible with PHP >= 7.0

* 2.0.14 Using PHPUnit < 6.0

* 2.0.13 Improved swift mailer test double

* 2.0.12 Added support for swift mailer

* 2.0.11 Added FixedDateTime

* 2.0.10 Doctrine DBAL: Added switch to hide fixture related warnings

* 2.0.9 Fixed bug in Doctrine rollBack()

* 2.0.8 Fixed bug in Doctrine Connection class

* 2.0.7 Improved Riak client

* 2.0.6 Improved Riak fingerprinting

* 2.0.5 Added Basho\Riak support

* 2.0.4 Improved fingerprinting for fixtures

* 2.0.2 Adapted TestContainerBuilder for Symfony >= 2.8

* 2.0.1 Improved TestContainerBuilder, documentation and code formatting

* 2.0.0 Upgraded to Symfony 3.0 

Release 1
---------

* 1.2.0 Upgraded to Symfony 2.8

* 1.1.1 Fixed issue in getTestBasePath()

* 1.0.3 Added support for unserializable results

* 1.0.2 Improved doctrine dbal transaction handling

* 1.0.1 useFixtures with empty argument disables fixtures

* 1.0.0 Stable release; Added support for Doctrine transactions
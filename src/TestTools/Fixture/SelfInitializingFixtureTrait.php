<?php

namespace TestTools\Fixture;

use TestTools\Fixture\FileFixture;
use TestTools\Fixture\Exception\FixtureException;
use TestTools\Fixture\Exception\FixtureNotFoundException;
use TestTools\Fixture\Exception\OfflineException;

/**
 * You can use this trait to easily make any existing class work with file based fixtures
 *
 * use TestTools\Fixture\SelfInitializingFixtureTrait;
 *
 * class Foo extends SomeBaseClass
 * {
 *     use SelfInitializingFixtureTrait;
 *
 *     public function bar($name, $type, array $baz = array())
 *     {
 *         return $this->callWithFixtures('bar', array($name, $type, $baz));
 *     }
 * }
 *
 * To avoid conflicts, all properties and methods either contain "fixture" in their name and/or are prefixed with "_"
 *
 * @author Michael Mayer <michael@lastzero.net>
 * @package TestTools
 * @license MIT
 */
trait SelfInitializingFixtureTrait
{
    /**
     * @var string
     */
    private $_fixturePath = false;

    /**
     * @var string
     */
    private $_fixturePrefix = 'fix';

    /**
     * @var bool
     */
    private $_fixtureOfflineMode = false;

    /**
     * The object which this trait should create fixtures for;
     * use 'parent' for the parent instance
     *
     * @var string|object
     */
    private $_fixtureInstance = 'parent';

    /**
     * Returns method caller as string (for debug output)
     *
     * @param int $traceIndex
     * @return string
     */
    protected function getFixtureCaller($traceIndex = 6)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $traceIndex + 1);
        $caller = $trace[$traceIndex];

        if (isset($caller['class'])) {
            $result = @$caller['class'] . '->' . @$caller['function'] . '()';
        } else {
            $result = @$caller['function'] . '()';
        }

        return $result;
    }

    /**
     * Enables self initializing fixtures (disabled by default)
     *
     * @param string $fixturePath
     */
    public function useFixtures($fixturePath)
    {
        if ($fixturePath) {
            $this->_fixturePath = FileFixture::normalizePath($fixturePath);
        } else {
            $this->disableFixtures();
        }
    }

    /**
     * Disables self initializing fixtures
     */
    public function disableFixtures()
    {
        $this->_fixturePath = false;
    }

    /**
     * Returns true, if self initializing fixtures are enabled
     *
     * @return bool
     */
    public function usesFixtures()
    {
        return ($this->_fixturePath != false);
    }

    /**
     * Sets a file name prefix for fixtures
     *
     * @throws FixtureException
     * @param string $prefix
     */
    public function setFixturePrefix($prefix)
    {
        if (empty($prefix)) {
            throw new FixtureException('Fixture prefix can not be empty');
        }

        $this->_fixturePrefix = $prefix;
    }

    /**
     * Returns the file name prefix for fixtures
     *
     * @return string
     */
    public function getFixturePrefix()
    {
        return $this->_fixturePrefix;
    }

    /**
     * Returns the fixture file path
     *
     * @return string
     */
    public function getFixturePath()
    {
        return $this->_fixturePath;
    }

    /**
     * Prevents any connection to external data source (database or web service)
     *
     * Can be used for testing only environments (Jenkins, Travis CI,...)
     */
    public function enableFixtureOfflineMode()
    {
        $this->_fixtureOfflineMode = true;
    }

    /**
     * Disables offline mode (default)
     */
    public function disableFixtureOfflineMode()
    {
        $this->_fixtureOfflineMode = false;
    }

    /**
     * Returns true, if offline mode is enabled
     *
     * @return bool
     */
    public function fixtureOfflineModeEnabled()
    {
        return (bool)$this->_fixtureOfflineMode;
    }

    /**
     * Wrapper that calls the parent function with file fixtures (if enabled)
     *
     * @param string $functionName
     * @param array $arguments
     * @param array $resultArguments
     * @throws \Exception
     * @return mixed
     */
    protected function callWithFixtures($functionName, array $arguments = array(), &$resultArguments = null)
    {
        if ($this->usesFixtures()) {
            // Determine fixture file name
            $fingerprintArguments = array();

            foreach ($arguments as $arg) {
                if (is_object($arg) && method_exists($arg, '__toString')) {
                    $fingerprintArg = (string)$arg;
                } else {
                    $fingerprintArg = $arg;
                }

                $fingerprintArguments[] = $fingerprintArg;
            }

            $fixture = new FileFixture($this->_fixturePath . FileFixture::getFilename($this->getFixturePrefix() . '_' . $functionName, $fingerprintArguments));

            // Try to find existing fixture file
            try {
                $fixture->find();

                $result = $fixture->getResult();

                // Throw exception or return value?
                if (is_object($result) && $result instanceof \Exception) {
                    throw $result;
                }

                $resultArguments = $fixture->getArguments();

                return $result;
            } catch (FixtureNotFoundException $e) {
                // No fixture found, the original method has to be called
            }
        }

        if ($this->fixtureOfflineModeEnabled()) {
            throw new OfflineException('Can not create fixture for ' . $functionName . '() in offline mode');
        }

        $throwException = false;

        // Catch exceptions to be able to create fixtures for them as well
        try {
            $result = call_user_func_array(array($this->_fixtureInstance, $functionName), $arguments);
        } catch (\Exception $e) {
            $result = $e;
            $throwException = true;
        }

        // Write return value / exception to file fixture
        if ($this->usesFixtures()) {
            $resultArguments = $arguments;
            $fixture->setResult($result);
            $fixture->setArguments($arguments);
            $fixture->save();
        }

        // Throw exception or return value?
        if ($throwException) {
            throw $result;
        }

        return $result;
    }
}
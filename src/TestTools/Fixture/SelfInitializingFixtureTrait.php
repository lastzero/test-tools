<?php

namespace TestTools\Fixture;

use TestTools\Fixture\FileFixture;
use TestTools\Fixture\Exception\FixtureNotFoundException;
use TestTools\Fixture\Exception\OfflineException;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
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
     * @var bool
     */
    private $_offlineMode = false;

    /**
     * Returns method caller as string (for debug output)
     *
     * @param int $traceIndex
     * @return string
     */
    public function getCaller($traceIndex = 6)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $traceIndex  + 1);
        $caller = $trace[$traceIndex];

        if(isset($caller['class'])) {
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
        $this->_fixturePath = FileFixture::normalizePath($fixturePath);
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
     * Prevents any connection to external data source (database or web service)
     *
     * Can be used for testing only environments (Jenkins, Travis CI,...)
     */
    public function enableOfflineMode()
    {
        $this->_offlineMode = true;
    }

    /**
     * Disables offline mode (default)
     */
    public function disableOfflineMode()
    {
        $this->_offlineMode = false;
    }

    /**
     * Returns true, if offline mode is enabled
     *
     * @return bool
     */
    public function offlineModeEnabled()
    {
        return (bool) $this->_offlineMode;
    }

    /**
     * Wrapper that calls the parent function with file fixtures (if enabled)
     *
     * @param string $functionName
     * @param array $params
     * @throws \Exception
     * @return mixed
     */
    protected function callWithFixtures($functionName, array $params = array())
    {
        if ($this->usesFixtures()) {
            $fingerprintArguments = array();

            foreach($params as $param) {
                if(is_object($param)) {
                    $arg = (string) $param;
                } else {
                    $arg = $param;
                }

                $fingerprintArguments[] = $arg;
            }

            $fixture = new FileFixture($this->_fixturePath . FileFixture::getFilename('sql_' . $functionName, $fingerprintArguments));

            try {
                $result = $fixture->getData();

                if(is_object($result) && $result instanceof \Exception) {
                    throw $result;
                }

                return $result;
            } catch (FixtureNotFoundException $e) {
                // No fixture found, the query has to be executed
            }
        }

        if($this->offlineModeEnabled()) {
            throw new OfflineException('Can not create fixture for ' . $functionName . '() in offline mode');
        }

        $throwException = false;

        try {
            $result = call_user_func_array(array('parent', $functionName), $params);
        } catch(\Exception $e) {
            $result = $e;
            $throwException = true;
        }

        if ($this->usesFixtures()) {
            $fixture->setData($result);
        }

        if($throwException) {
            throw $result;
        }

        return $result;
    }
}
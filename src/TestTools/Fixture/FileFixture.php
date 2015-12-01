<?php

namespace TestTools\Fixture;

use TestTools\Fixture\Exception\FixtureEmptyFilenameException;
use TestTools\Fixture\Exception\FixtureNotFoundException;
use TestTools\Fixture\Exception\FixtureNotValidException;
use TestTools\Fixture\Exception\FixtureInvalidDirectoryException;

/**
 * File fixture for general usage
 * 
 * Classes that properly support Dependency Injection can utilize the
 * injection of test adapters with fixture support to avoid the need for
 * modification of any production code.
 *
 * A good explaination on the topic was written by Martin Fowler:
 * 
 *   http://martinfowler.com/bliki/SelfInitializingFake.html
 *
 * @author Michael Mayer <michael@lastzero.net>
 * @package TestTools
 * @license MIT
 */
class FileFixture
{
    protected $filename;
    protected $data = array('result' => null, 'args' => array());

    public function __construct($filename)
    {
        $this->setFilename($filename);
    }
    
    public function setFilename($filename) 
    {
        if (empty($filename)) {
            throw new FixtureEmptyFilenameException('Empty filename');
        }

        $this->filename = $filename;
    }

    public function find() {
        if (!file_exists($this->filename)) {
            throw new FixtureNotFoundException('File not found: ' . $this->filename);
        }

        $data = unserialize(file_get_contents($this->filename));

        if(!array_key_exists('result', $data) || !array_key_exists('args', $data)) {
            throw new FixtureNotValidException('Data in fixture file is not valid');
        }

        $this->data = $data;

        return $this;
    }

    public function save() {
        file_put_contents($this->filename, serialize($this->data));
        return $this;
    }

    public function getResult()
    {
        if (is_object($this->data['result']) && $this->data['result'] instanceof NonSerializableExceptionContainer) {
            return $this->data['result']->recreate();
        } else {
            return $this->data['result'];
        }
    }

    public function getArguments()
    {
        return $this->data['args'];
    }

    private function transformNonSerializableException($exception)
    {
        $transformed = \Mockery::mock(get_class($exception));
        return $transformed;
    }

    public function setResult($result)
    {
        try {
            serialize($result);
        } catch (\Exception $e) {
            if ($result instanceof \Exception) {
                $result = new NonSerializableExceptionContainer($result);
            } else {
                $result = 'Result could not be serialized: ' . print_r($result, true);
            }
        }

        $this->data['result'] = $result;

        return $this;
    }

    public function setArguments(array $arguments)
    {
        foreach($arguments as &$argument) {
            try {
                serialize($argument);
            } catch (\Exception $e) {
                $argument = 'Argument could not be serialized: ' . print_r($argument, true);
            }
        }

        $this->data['args'] = $arguments;

        return $this;
    }

    public static function filterAlphanumeric($string)
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $string);
    }

    public static function normalizePath($directory)
    {
        $result = realpath($directory);

        if (empty($result)) {
            throw new FixtureInvalidDirectoryException('Invalid directory: ' . $directory);
        }

        return $result . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns fixture filename (includes md5 hash for large parameters)
     *
     * @param string $name
     * @param mixed $arguments
     * @return string
     */
    public static function getFilename($name, $arguments = false)
    {
        if (!$arguments) {
            $filename = self::filterAlphanumeric($name);
        } else {
            $fingerprint = self::filterAlphanumeric(strtr(print_r($arguments, true), array('=' => '_', 'Array' => 'array_')));

            if (strlen($fingerprint) > 40) {
                $fingerprint = md5(print_r($arguments, true));
            }

            $filename = self::filterAlphanumeric($name) . '.' . $fingerprint;
        }

        return $filename . '.fix';
    }
}

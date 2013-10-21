<?php

namespace TestTools\Fixture;

use TestTools\Fixture\Exception\FixtureEmptyFilenameException;
use TestTools\Fixture\Exception\FixtureNotFoundException;
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
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package TestTools
 * @license MIT
 */
class FileFixture
{
    protected $filename;

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

    public function getData()
    {
        if (!file_exists($this->filename)) {
            throw new FixtureNotFoundException('File not found: ' . $this->filename);
        }

        return unserialize(file_get_contents($this->filename));
    }

    public function setData($data)
    {
        file_put_contents($this->filename, serialize($data));
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
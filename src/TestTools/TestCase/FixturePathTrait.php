<?php

namespace TestTools\TestCase;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package TestTools
 * @license MIT
 */
trait FixturePathTrait
{
    private $testBasePath = '';
    private $fixturePath = '';
    private $testsDirectory = '/tests/';
    private $fixturesDirectory = '_fixtures/';
    private $testsPostfix = 'Test.php';

    protected function getTestFilename()
    {
        $reflector = new \ReflectionClass($this);
        $testFilename = $reflector->getFileName();

        return $testFilename;
    }

    protected function getTestDirectory()
    {
        $testFilename = $this->getTestFilename();
        $testDirectory = dirname($testFilename);

        return $testDirectory;
    }

    protected function setFixturePath($path)
    {
        // @codeCoverageIgnoreStart
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        // @codeCoverageIgnoreEnd

        $this->fixturePath = (string)$path;
    }

    protected function getFixturePath()
    {
        if ($this->fixturePath == '') {
            $testFilename = $this->getTestFilename();

            $subDirectory = substr(
                $testFilename,
                strripos($testFilename, $this->testsDirectory) + strlen($this->testsDirectory),
                strlen($this->testsPostfix) * -1
            );

            $fixturePath = $this->getTestBasePath() . $this->fixturesDirectory . $subDirectory;

            $this->setFixturePath($fixturePath);
        }

        return $this->fixturePath;
    }

    protected function setTestBasePath($path)
    {
        $this->testBasePath = (string)$path;
    }

    protected function getTestBasePath()
    {
        if ($this->testBasePath == '') {
            $testDirectory = $this->getTestDirectory();

            $basePath = substr(
                $testDirectory,
                0,
                strripos($testDirectory, $this->testsDirectory) + strlen($this->testsDirectory)
            );

            $this->setTestBasePath($basePath);
        }

        return $this->testBasePath;
    }

    protected function getBasePath()
    {
        return realpath($this->getTestBasePath() . '/..');
    }
}
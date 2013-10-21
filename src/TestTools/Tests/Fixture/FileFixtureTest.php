<?php

namespace TestTools\Tests\Fixture;

use TestTools\Fixture\FileFixture;
use TestTools\TestCase\UnitTestCase;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package TestTools
 * @license MIT
 */
class FileFixtureTest extends UnitTestCase {
    public function testFilterAlphanumeric () {
        $this->assertEquals('_abc123', FileFixture::filterAlphanumeric('_-abc@#$%^&*(123,./<>?:"{}|\]['));
        $this->assertEquals('', FileFixture::filterAlphanumeric(''));        
        $this->assertEquals(array('php_'), FileFixture::filterAlphanumeric(array('php!@#$%^%_*()')));
    }
    
    public function testGetFilename () {
        $this->assertEquals('foo_barbaz.fix', FileFixture::getFilename('foo_bar.baz&*()'));
        $this->assertEquals('foo_barbaz.array_a_b.fix', FileFixture::getFilename('foo_bar.baz&*()', array('a' => 'b')));
        $this->assertEquals('foo.bar.fix', FileFixture::getFilename('foo!', 'bar'));
        $this->assertEquals('foo.fix', FileFixture::getFilename('foo'));
        $this->assertEquals('GoOo.b7f3e6756b2ca19c4b06f5e95061e342.fix', FileFixture::getFilename('GoOo', 'e5v8snjpv0pjsev4fjp0ws4tfghsge;]-c3seecfjhisfhijjijkjmcs8jvn'));
    }
    
    public function testNormalizePath () {
        $this->assertEquals(getcwd() . DIRECTORY_SEPARATOR, FileFixture::normalizePath(''));
        $this->assertEquals(__DIR__ . DIRECTORY_SEPARATOR, FileFixture::normalizePath(__DIR__));
        $this->assertEquals(__DIR__ . DIRECTORY_SEPARATOR, FileFixture::normalizePath(__DIR__ . DIRECTORY_SEPARATOR));
    }
    
    public function testNormalizePathException () {
        $this->setExpectedException('TestTools\Fixture\Exception\FixtureInvalidDirectoryException');
        
        FileFixture::normalizePath('/a/b/c');
    }
    
    public function testConstructor () {
        $this->setExpectedException('TestTools\Fixture\Exception\FixtureEmptyFilenameException');

        $fixture = new FileFixture('');
    }

    public function testGetData () {
        $fixture = new FileFixture($this->getFixturePath() . '/fixture_test_get_data.fix');
        $this->assertEquals(array('a' => 'b', array('b' => 'c')), $fixture->getData());
    }
    
    /**
     * @expectedException \TestTools\Fixture\Exception\FixtureNotFoundException
     */
    public function testGetDataException () {
        $fixture = new FileFixture($this->getFixturePath() . '/doesnotexist.fix');
        $fixture->getData();
    }

    public function testSetData () {
        $fixture = new FileFixture($this->getFixturePath() . '/fixture_test_set_data.fix');
        $fixture->setData(array('a' => 'b', array('x' => 'y')));
        $this->assertEquals('a:2:{s:1:"a";s:1:"b";i:0;a:1:{s:1:"x";s:1:"y";}}', file_get_contents($this->getFixturePath() . '/fixture_test_set_data.fix'));
    }
}
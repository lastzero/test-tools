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
    public function testBasePath () {
        $result = $this->getBasePath();
        $this->assertEquals(0, strpos(strrev($result), strrev('TestTools')));
    }

    public function testFixturePath () {
        $result = $this->getFixturePath();
        $this->assertEquals(36, strpos(strrev($result), strrev('TestTools')));
        $this->assertEquals(21, strpos(strrev($result), strrev('_fixture')));
    }

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

        new FileFixture('');
    }

    public function testFind () {
        $fixture = new FileFixture($this->getFixturePath() . '/fixture_test_find.fix');
        $fixture->find();
        $this->assertEquals(array('a' => 'b', array('x' => 'y')), $fixture->getResult());
    }
    
    /**
     * @expectedException \TestTools\Fixture\Exception\FixtureNotFoundException
     */
    public function testFindException () {
        $fixture = new FileFixture($this->getFixturePath() . '/doesnotexist.fix');
        $fixture->find();
    }

    public function testSave () {
        $filename = $this->getFixturePath() . '/fixture_test_save.fix';

        if(file_exists($filename)) {
            unlink($filename);
        }

        $fixture = new FileFixture($filename);
        $arguments = array('1', '2', '3');
        $values = array('a' => 'b', array('x' => 'y'));

        $fixture->setResult($values);
        $fixture->setArguments($arguments);
        $fixture->save();

        $this->assertEquals(
            'a:2:{s:6:"result";a:2:{s:1:"a";s:1:"b";i:0;a:1:{s:1:"x";s:1:"y";}}s:4:"args";a:3:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";}}',
            file_get_contents($filename)
        );
    }

    public function testSetArguments()
    {
        $object = new NotSerializable();

        $result = 123;
        $arguments = array(
            'bar',
            $object
        );

        $filename = $this->getFixturePath() . '/not_serializable_argument.fix';
        $fixture = new FileFixture($filename);
        $fixture->setArguments($arguments);
        $fixture->setResult($result);

        $fixture->save();

        $serializedObject = 'Argument could not be serialized: TestTools\Tests\Fixture\NotSerializable Object' . "\n(\n)\n";

        $expected = array(
            'bar',
            $serializedObject
        );

        $this->assertEquals($expected, $fixture->getArguments());
    }
}
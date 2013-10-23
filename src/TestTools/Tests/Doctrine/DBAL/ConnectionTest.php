<?php

namespace TestTools\Tests\Doctrine\DBAL;

use TestTools\Fixture\Exception\OfflineException;
use TestTools\TestCase\UnitTestCase;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package TestTools
 * @license MIT
 */
class ConnectionTest extends UnitTestCase
{
    /**
     * @var \TestTools\Doctrine\DBAL\Connection
     */
    protected $db;

    public function setUp()
    {
        $this->db = $this->get('dbal.connection');
    }

    public function testUsesFixtures()
    {
        $this->assertTrue($this->db->usesFixtures());
    }

    public function testFixtureOfflineMode()
    {
        $this->assertFalse($this->db->fixtureOfflineModeEnabled());
        $this->db->enableFixtureOfflineMode();
        $this->assertTrue($this->db->fixtureOfflineModeEnabled());

        try {
            $this->db->fetchAll('SELECT * FROM example');
            $this->fail('OfflineException was not thrown');
        } catch(OfflineException $e) {
            // OK
        }

        $this->db->disableFixtureOfflineMode();
        $this->assertFalse($this->db->fixtureOfflineModeEnabled());
    }

    public function testFetchAll()
    {
        $expected = array(
            array('id' => 1, 'name' => 'Foo', 'email' => 'foo@example.com'),
            array('id' => 2, 'name' => 'Bar', 'email' => 'bar@example.com'),
            array('id' => 4, 'name' => 'Baz', 'email' => 'baz@example.com'),
            array('id' => 5, 'name' => 'New', 'email' => 'new@example.com')
        );

        $result = $this->db->fetchAll('SELECT * FROM users');

        $this->assertEquals($expected, $result);
    }

    public function testFetchAssoc()
    {
        $expected = array('id' => 2, 'name' => 'Bar', 'email' => 'bar@example.com');

        $result = $this->db->fetchAssoc('SELECT * FROM users WHERE id = 2');

        $this->assertEquals($expected, $result);
    }

    public function testFetchArray()
    {
        $expected = array(2, 'Bar', 'bar@example.com');

        $result = $this->db->fetchArray('SELECT * FROM users WHERE id = 2');

        $this->assertEquals($expected, $result);
    }

    public function testFetchColumn()
    {
        $expected = 'Bar';

        $result = $this->db->fetchColumn('SELECT name FROM users WHERE id = 2');

        $this->assertEquals($expected, $result);
    }

    public function testInsert()
    {
        $expected = 1;

        $row = array('name' => 'Baz', 'email' => 'baz@example.com');

        $result = $this->db->insert('users', $row);

        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     */
    public function testInsertException()
    {
        $row = array('name' => 'Baz', 'foo' => 'bar', 'email' => 'baz@example.com');

        $this->db->insert('users', $row);
    }

    /**
     * @depends testInsert
     */
    public function testLastInsertId()
    {
        $expected = 5;

        $row = array('name' => 'New', 'email' => 'new@example.com');

        $this->db->insert('users', $row);

        $result = $this->db->lastInsertId();

        $this->assertEquals($expected, $result);
    }

    /**
     * @depends testInsert
     */
    public function testUpdate()
    {
        $expected = 1;

        $result = $this->db->update('users', array('name' => 'Changed'), array('id' => 3));

        $this->assertEquals($expected, $result);
    }

    /**
     * @depends testInsert
     */
    public function testDelete()
    {
        $expected = 1;

        $result = $this->db->delete('users', array('id' => 3));

        $this->assertEquals($expected, $result);
    }
}
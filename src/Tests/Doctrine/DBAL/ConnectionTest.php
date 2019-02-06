<?php

namespace TestTools\Tests\Doctrine\DBAL;

use TestTools\Fixture\Exception\OfflineException;
use TestTools\TestCase\UnitTestCase;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @package TestTools
 * @license MIT
 */
class ConnectionTest extends UnitTestCase
{
    /**
     * @var \TestTools\Doctrine\DBAL\Connection
     */
    protected $db;

    public function setUp(): void
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
        } catch (OfflineException $e) {
            // OK
        }

        $this->db->disableFixtureOfflineMode();
        $this->assertFalse($this->db->fixtureOfflineModeEnabled());
    }

    public function testFetchAll()
    {
        $expected = array(
            array('id' => 1, 'username' => 'Foo', 'email' => 'foo@bar.com'),
            array('id' => 2, 'username' => 'Michael', 'email' => 'michael@bar.com'),
            array('id' => 3, 'username' => 'Alex', 'email' => 'alex@foo.com'),
            array('id' => 4, 'username' => 'Bender', 'email' => 'bender@ilovebender.com'),
            array('id' => 5, 'username' => 'Bill', 'email' => 'bill@bar.com')
        );

        $result = $this->db->fetchAll('SELECT * FROM users');

        $this->assertEquals($expected, $result);
    }

    public function testFetchAssoc()
    {
        $expected = array('id' => 2, 'username' => 'Michael', 'email' => 'michael@bar.com');

        $result = $this->db->fetchAssoc('SELECT * FROM users WHERE id = 2');

        $this->assertEquals($expected, $result);
    }

    public function testFetchArray()
    {
        $expected = array(2, 'Michael', 'michael@bar.com');

        $result = $this->db->fetchArray('SELECT * FROM users WHERE id = 2');

        $this->assertEquals($expected, $result);
    }

    public function testFetchColumn()
    {
        $expected = 'Michael';

        $result = $this->db->fetchColumn('SELECT username FROM users WHERE id = 2');

        $this->assertEquals($expected, $result);
    }

    public function testInsert()
    {
        $expected = 1;

        $row = array('username' => 'Jens', 'email' => 'jens@testtools.com');

        $result = $this->db->insert('users', $row);

        $this->assertEquals($expected, $result);
    }

    public function testInsertException()
    {
        $this->expectException('\Doctrine\DBAL\DBALException');

        $row = array('username' => 'Baz', 'foo' => 'bar', 'email' => 'baz@example.com');

        $this->db->insert('users', $row);
    }

    /**
     * @depends testInsert
     */
    public function testLastInsertId()
    {
        $expected = 7;

        $row = array('username' => 'New', 'email' => 'new@example.com');

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

        $result = $this->db->update('users', array('username' => 'Changed'), array('id' => 3));

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

    public function testCommit()
    {
        $this->db->beginTransaction();
        $this->db->commit();
        $this->assertTrue(true);
    }

    public function testRollBack()
    {
        $this->db->beginTransaction();
        $this->db->rollBack();
        $this->assertTrue(true);
    }

    public function testGetDatabasePlatform()
    {
        $result = $this->db->getDatabasePlatform();
        $this->assertInstanceOf('\Doctrine\DBAL\Platforms\MySqlPlatform', $result);
    }

    public function testQuote()
    {
        $original = 'A';
        $resultUppercase = $this->db->quote(strtoupper($original));

        $this->assertEquals("'A'", $resultUppercase);

        $resultLowercase = $this->db->quote(strtolower($original));

        $this->assertEquals("'a'", $resultLowercase);
    }

    public function testQuoteIdentifier()
    {
        $original = 'A';
        $resultUppercase = $this->db->quoteIdentifier(strtoupper($original));

        $this->assertEquals("`A`", $resultUppercase);

        $resultLowercase = $this->db->quoteIdentifier(strtolower($original));

        $this->assertEquals("`a`", $resultLowercase);
    }
}
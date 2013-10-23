<?php

namespace TestTools\Doctrine\DBAL;

use Doctrine\DBAL\Connection as DoctrineDBALConnection;
use TestTools\Fixture\SelfInitializingFixtureTrait;

/**
 * This class works as a wrapper for the standard Doctrine DBAL connection class.
 *
 * See wrapperClass option:
 * http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html
 *
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package TestTools
 * @license MIT
 */
class Connection extends DoctrineDBALConnection
{
    use SelfInitializingFixtureTrait;

    /**
     * Outputs the caller method name so that accidental queries are not unnoticed
     *
     * Using echo() is safe, since fixtures should be enabled on the command line while running PHPUnit only!
     *
     * @return bool
     */
    public function connect()
    {
        if ($this->usesFixtures()) {
            echo ' [SQL CONNECT BY "' . $this->getFixtureCaller() . '"] ';
        }

        return parent::connect();
    }

    /**
     * Since query() returns a Statement object containing a non-serializable PHP resource, there is no easy
     * way to create fixtures for it.
     *
     * @return \Doctrine\DBAL\Driver\Statement|mixed|null
     */
    public function query()
    {
        if ($this->fixtureOfflineModeEnabled()) {
            // Ignore query
            return null;
        }

        if ($this->usesFixtures()) {
            echo 'WARNING: query() does not work with fixtures - please use other SQL methods';
        }

        return call_user_func_array(array('parent', 'query'), func_get_args());
    }

    public function fetchAll($sql, array $params = array(), $types = array())
    {
        return $this->callWithFixtures('fetchAll', func_get_args());
    }

    public function fetchAssoc($statement, array $params = array())
    {
        return $this->callWithFixtures('fetchAssoc', func_get_args());
    }

    public function fetchArray($statement, array $params = array())
    {
        return $this->callWithFixtures('fetchArray', func_get_args());
    }

    public function fetchColumn($statement, array $params = array(), $colnum = 0)
    {
        return $this->callWithFixtures('fetchColumn', func_get_args());
    }

    public function delete($tableName, array $identifier, array $types = array())
    {
        return $this->callWithFixtures('delete', func_get_args());
    }

    public function update($tableName, array $data, array $identifier, array $types = array())
    {
        return $this->callWithFixtures('update', func_get_args());
    }

    public function executeUpdate($query, array $params = array(), array $types = array())
    {
        return $this->callWithFixtures('executeUpdate', func_get_args());
    }

    public function insert($tableName, array $data, array $types = array())
    {
        return $this->callWithFixtures('insert', func_get_args());
    }

    public function quoteIdentifier($str)
    {
        return $this->callWithFixtures('quoteIdentifier', func_get_args());
    }

    public function quote($input, $type = null)
    {
        return $this->callWithFixtures('quote', func_get_args());
    }

    public function project($query, array $params, Closure $function)
    {
        return $this->callWithFixtures('project', func_get_args());
    }

    /**
     * This is a bit of a hack, since static fixtures will always return the same ID. Make sure to write
     * your tests accordingly:
     *
     * - Using lastInsertId() once after insert() is safe.
     * - Using it multiple times will return the same value again.
     *
     * @param string|null $seqName
     * @return mixed|string
     */
    public function lastInsertId($seqName = null)
    {
        return $this->callWithFixtures('lastInsertId', func_get_args());
    }
}
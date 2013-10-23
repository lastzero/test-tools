<?php

namespace TestTools\Doctrine\DBAL;

use Closure;
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
        $arguments = func_get_args();
        return $this->callWithFixtures('fetchAll', $arguments);
    }

    public function fetchAssoc($statement, array $params = array())
    {
        $arguments = func_get_args();
        return $this->callWithFixtures('fetchAssoc', $arguments);
    }

    public function fetchArray($statement, array $params = array())
    {
        $arguments = func_get_args();
        return $this->callWithFixtures('fetchArray', $arguments);
    }

    public function fetchColumn($statement, array $params = array(), $colnum = 0)
    {
        $arguments = func_get_args();
        return $this->callWithFixtures('fetchColumn', $arguments);
    }

    public function delete($tableName, array $identifier, array $types = array())
    {
        $arguments = func_get_args();
        return $this->callWithFixtures('delete', $arguments);
    }

    public function update($tableName, array $data, array $identifier, array $types = array())
    {
        $arguments = func_get_args();
        return $this->callWithFixtures('update', $arguments);
    }

    public function executeUpdate($query, array $params = array(), array $types = array())
    {
        $arguments = func_get_args();
        return $this->callWithFixtures('executeUpdate', $arguments);
    }

    public function insert($tableName, array $data, array $types = array())
    {
        $arguments = func_get_args();
        return $this->callWithFixtures('insert', $arguments);
    }

    public function quoteIdentifier($str)
    {
        $arguments = func_get_args();
        return $this->callWithFixtures('quoteIdentifier', $arguments);
    }

    public function quote($input, $type = null)
    {
        $arguments = func_get_args();
        return $this->callWithFixtures('quote', $arguments);
    }

    public function project($query, array $params, Closure $function)
    {
        $arguments = func_get_args();
        return $this->callWithFixtures('project', $arguments);
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
        $arguments = func_get_args();
        return $this->callWithFixtures('lastInsertId', $arguments);
    }
}
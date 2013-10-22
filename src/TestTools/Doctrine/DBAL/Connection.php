<?php

namespace TestTools\Doctrine\DBAL;

use Doctrine\DBAL\Connection as DoctrineDBALConnection;
use TestTools\Fixture\SelfInitializingFixtureTrait;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package TestTools
 * @license MIT
 */
class Connection extends DoctrineDBALConnection
{
    use SelfInitializingFixtureTrait;

    public function connect()
    {
        if ($this->usesFixtures()) {
            echo ' [SQL CONNECT BY "' . $this->getCaller() . '"] ';
        }

        return parent::connect();
    }

    public function query()
    {
        if ($this->offlineModeEnabled()) {
            return;
        }

        if ($this->usesFixtures()) {
            echo 'WARNING: query() does not work with fixtures - please use other SQL methods';
        }

        return call_user_func_array(array('parent', 'query'), func_get_args());
    }

    public function fetchAll($sql, array $params = array(), $types = array())
    {
        return $this->callWithFixtures('fetchAll', array($sql, $params, $types));
    }

    public function fetchAssoc($statement, array $params = array())
    {
        return $this->callWithFixtures('fetchAssoc', array($statement, $params));
    }

    public function fetchArray($statement, array $params = array())
    {
        return $this->callWithFixtures('fetchArray', array($statement, $params));
    }

    public function fetchColumn($statement, array $params = array(), $colnum = 0)
    {
        return $this->callWithFixtures('fetchColumn', array($statement, $params, $colnum));
    }

    public function delete($tableName, array $identifier, array $types = array())
    {
        return $this->callWithFixtures('delete', array($tableName, $identifier, $types));
    }

    public function update($tableName, array $data, array $identifier, array $types = array())
    {
        return $this->callWithFixtures('update', array($tableName, $data, $identifier, $types));
    }

    public function executeUpdate($query, array $params = array(), array $types = array())
    {
        return $this->callWithFixtures('executeUpdate', array($query, $params, $types));
    }

    public function insert($tableName, array $data, array $types = array())
    {
        return $this->callWithFixtures('insert', array($tableName, $data, $types));
    }

    public function quoteIdentifier($str)
    {
        return $this->callWithFixtures('quoteIdentifier', array($str));
    }

    public function quote($input, $type = null)
    {
        return $this->callWithFixtures('quote', array($input, $type));
    }

    public function project($query, array $params, Closure $function)
    {
        return $this->callWithFixtures('project', array($query, $params, $function));
    }

    public function lastInsertId($seqName = null)
    {
        return $this->callWithFixtures('lastInsertId', array($seqName));
    }
}
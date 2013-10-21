<?php

namespace TestTools\Doctrine;

use Doctrine\DBAL\Connection;
use TestTools\Fixture\FileFixture;
use TestTools\Fixture\Exception\FixtureNotFoundException;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package TestTools
 * @license MIT
 */
class FixtureConnection extends Connection
{
    private $_fixturePath = false;
    private $_disableQuery = false;

    public function connect()
    {
        if ($this->_fixturePath) {
            echo ' [SQL CONNECT BY "' . $this->getCaller() . '"] ';
        }

        return parent::connect();
    }

    public function getCaller()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
        $caller = $trace[3];
        return @$caller['class'] . '->' . @$caller['function'] . '()';
    }

    public function useFixtures($fixturePath)
    {
        $this->_fixturePath = FileFixture::normalizePath($fixturePath);
    }

    public function disableFixtures()
    {
        $this->_fixturePath = false;
    }

    public function usesFixtures()
    {
        return ($this->_fixturePath != false);
    }

    public function disableDirectQueries()
    {
        $this->_disableQuery = true;
    }

    public function enableDirectQueries()
    {
        $this->_disableQuery = false;
    }

    protected function callWithFixtures($functionName, $params)
    {
        if ($this->_fixturePath) {
            $fixture = new FileFixture($this->_fixturePath . FileFixture::getFilename('sql_' . $functionName, $params));

            try {
                $result = $fixture->getData();
                return $result;
            } catch (FixtureNotFoundException $e) {
                // No fixture found, the query has to be executed
            }
        }

        $result = call_user_func_array(array('parent', $functionName), $params);

        if ($this->_fixturePath) {
            $fixture->setData($result);
        }

        return $result;
    }

    public function query()
    {
        if ($this->_disableQuery) {
            return;
        }

        if ($this->_fixturePath) {
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
}
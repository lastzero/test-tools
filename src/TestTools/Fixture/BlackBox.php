<?php

namespace TestTools\Fixture;

use TestTools\Fixture\SelfInitializingFixtureTrait;
use TestTools\Fixture\Exception\FixtureException;

class BlackBox
{
    use SelfInitializingFixtureTrait;

    public function setFixtureInstance($instance)
    {
        if(empty($instance)) {
            throw new FixtureException('Instance can not be empty');
        }

        $this->_fixtureInstance = $instance;
    }
}
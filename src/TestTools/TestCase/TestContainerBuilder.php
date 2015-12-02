<?php

namespace TestTools\TestCase;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @package TestTools
 * @license MIT
 */
class TestContainerBuilder extends ContainerBuilder
{
    public function __clone()
    {
        $this->reset();
    }
}
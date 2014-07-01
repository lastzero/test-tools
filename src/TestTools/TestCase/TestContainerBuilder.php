<?php

namespace TestTools\TestCase;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package TestTools
 * @license MIT
 */
class TestContainerBuilder extends ContainerBuilder {
    public function clearInstances() {
        $this->services = array();
        $this->set('service_container', $this);
    }
}
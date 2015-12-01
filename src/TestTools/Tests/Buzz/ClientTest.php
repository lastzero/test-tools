<?php

namespace TestTools\Tests\Buzz;

use Buzz\Message\Request;
use Buzz\Message\Response;
use TestTools\TestCase\UnitTestCase;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @package TestTools
 * @license MIT
 */
class ClientTest extends UnitTestCase
{
    /**
     * @var \TestTools\Buzz\Client
     */
    protected $buzz;

    public function setUp()
    {
        $this->buzz = $this->get('buzz.fixture');
    }

    public function testUsesFixtures()
    {
        $this->assertTrue($this->buzz->usesFixtures());
    }

    public function testSend()
    {
        $request = new Request('HEAD', '/', 'http://google.com');
        $response = new Response();

        $this->buzz->send($request, $response);

        $result = $response->getStatusCode();

        $this->assertEquals(200, $result);
    }
}
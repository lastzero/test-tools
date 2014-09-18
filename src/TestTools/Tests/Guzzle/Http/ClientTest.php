<?php

namespace TestTools\Tests\Guzzle\Http;

use TestTools\TestCase\UnitTestCase;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package TestTools
 * @license MIT
 */
class ClientTest extends UnitTestCase
{
    /**
     * @var \Guzzle\Http\Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = $this->get('guzzle.client');
    }

    protected function restRequest($method, $location)
    {
        $headers = null;
        $body = null;
        $options = array();

        $request = $this->client->createRequest($method, $location, $headers, $body, $options);
        $response = $request->send();
        $result = $response->json();

        return $result;
    }

    public function testRequestFactoryUsesFixtures()
    {
        $requestFactory = $this->get('guzzle.request.factory');

        $this->assertTrue($requestFactory->usesFixtures());
    }

    /**
     * @expectedException \Guzzle\Http\Exception\CurlException
     */
    public function testSendTimeoutException()
    {
        $request = $this->client->get('http://unknown.domain/');
        $request->send();
    }

    public function testSend()
    {
        $request = $this->client->get('http://date.jsontest.com/');
        $this->assertEquals('new', $request->getState());
        $response = $request->send();
        $expectedType = 'application/json; charset=ISO-8859-1';
        $this->assertEquals($expectedType, $response->getContentType());
        $result = $response->json();
        $expectedResult = array(
            'time' => '08:43:25 PM',
            'milliseconds_since_epoch' => 1411073005696,
            'date' => '09-18-2014'
        );
        $this->assertEquals($expectedResult, $result);
    }
}
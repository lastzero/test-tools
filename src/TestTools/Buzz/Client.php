<?php
namespace TestTools\Buzz;

use TestTools\Fixture\BlackBox;
use Buzz\Client\ClientInterface;
use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;

class Client extends BlackBox implements ClientInterface {
    public function __construct(ClientInterface $client)
    {
        $this->setFixturePrefix('buzz');
        $this->setFixtureInstance($client);
    }

    public function send(RequestInterface $request, MessageInterface $response)
    {
        $arguments = func_get_args();

        $result = $this->callWithFixtures('send', $arguments);

        /** @var MessageInterface $resultResponse */
        $resultResponse = $arguments[1];
        $response->setHeaders($resultResponse->getHeaders());
        $response->setContent($resultResponse->getContent());

        return $result;
    }
}
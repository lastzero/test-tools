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
        $result = $this->callWithFixtures('send', func_get_args(), $resultArguments);

        /** @var MessageInterface $resultResponse */
        $resultResponse = $resultArguments[1];
        $response->setHeaders($resultResponse->getHeaders());
        $response->setContent($resultResponse->getContent());

        return $result;
    }
}
<?php

namespace TestTools\Guzzle\Http;

use Guzzle\Http\Message\Request as GuzzleRequest;
use TestTools\Fixture\SelfInitializingFixtureTrait;

/**
 * HTTP request class to send requests
 */
class Request extends GuzzleRequest
{
    use SelfInitializingFixtureTrait;

    public function send()
    {
        return $this->callWithFixtures('send', array($this->__toString()));
    }

    public function getResponse()
    {
        $result = $this->response;

        return $result;
    }
}

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
        $body = '';

        if(property_exists($this, 'body')) {
            $body = $this->body;
        }

        $fingerprint = sha1(serialize(array($body, $this->getUrl(), $this->getMethod(), $this->getQuery(true))));
        return $this->callWithFixtures('send', array($fingerprint));
    }
}

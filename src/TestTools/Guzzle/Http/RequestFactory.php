<?php

namespace TestTools\Guzzle\Http;

use Guzzle\Http\Message\RequestFactory as GuzzleRequestFactory;
use TestTools\Fixture\SelfInitializingFixtureTrait;

class RequestFactory extends GuzzleRequestFactory
{
    use SelfInitializingFixtureTrait;

    /** @var string Class to instantiate for requests with no body */
    protected $requestClass = 'TestTools\\Guzzle\\Http\\Request';

    /** @var string Class to instantiate for requests with a body */
    protected $entityEnclosingRequestClass = 'TestTools\\Guzzle\\Http\\EntityEnclosingRequest';

    public function create($method, $url, $headers = null, $body = null, array $options = array())
    {
        $method = strtoupper($method);

        if ($method == 'GET' || $method == 'HEAD' || $method == 'TRACE') {
            // Handle non-entity-enclosing request methods
            $request = new $this->requestClass($method, $url, $headers);
            $request->useFixtures($this->getFixturePath());
            $request->setFixturePrefix($this->getFixturePrefix());
            if ($body) {
                // The body is where the response body will be stored
                $type = gettype($body);
                if ($type == 'string' || $type == 'resource' || $type == 'object') {
                    $request->setResponseBody($body);
                }
            }
        } else {
            // Create an entity enclosing request by default
            $request = new $this->entityEnclosingRequestClass($method, $url, $headers);
            $request->useFixtures($this->getFixturePath());
            $request->setFixturePrefix($this->getFixturePrefix());
            if ($body || $body === '0') {
                // Add POST fields and files to an entity enclosing request if an array is used
                if (is_array($body) || $body instanceof Collection) {
                    // Normalize PHP style cURL uploads with a leading '@' symbol
                    foreach ($body as $key => $value) {
                        if (is_string($value) && substr($value, 0, 1) == '@') {
                            $request->addPostFile($key, $value);
                            unset($body[$key]);
                        }
                    }
                    // Add the fields if they are still present and not all files
                    $request->addPostFields($body);
                } else {
                    // Add a raw entity body body to the request
                    $request->setBody($body, (string)$request->getHeader('Content-Type'));
                    if ((string)$request->getHeader('Transfer-Encoding') == 'chunked') {
                        $request->removeHeader('Content-Length');
                    }
                }
            }
        }

        if ($options) {
            $this->applyOptions($request, $options);
        }

        return $request;
    }
}

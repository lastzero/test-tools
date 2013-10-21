<?php

namespace TestTools\TestCase;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package TestTools
 * @license MIT
 */
class WebTestCase extends SymfonyWebTestCase
{
    use FixturePathTrait;

    /** @var \Symfony\Bundle\FrameworkBundle\Client */
    protected $client = null;

    public function setUp()
    {
        $this->client = $this->getClient();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->client = null;
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function getClient()
    {
        $result = static::createClient(
            [
                'environment' => 'test',
                'debug' => false
            ]
        );

        $container = $result->getContainer();

        $this->configureFixtures($container);

        return $result;
    }

    protected function configureFixtures(ContainerInterface $client)
    {
        // Does nothing by default
    }

    /**
     * Performs a HTTP request
     *
     * @param string $method        The request method
     * @param string $uri           The URI to fetch
     * @param array $parameters    The Request parameters
     * @param array $files         The files
     * @param array $server        The server parameters (HTTP headers are referenced with a HTTP_ prefix as PHP does)
     * @param string $content       The raw body data
     * @param Boolean $changeHistory Whether to update the history or not (only used internally for back(), forward(), and reload())
     *
     * @return \Symfony\Component\HttpFoundation\Response A Response instance
     */
    protected function request($method, $uri, array $parameters = array(), array $files = array(), array $server = array(), $content = null, $changeHistory = true)
    {
        $client = $this->getClient();
        call_user_func_array(array($client, 'request'), array($method, $uri, $parameters, $files, $server, $content, $changeHistory));
        $response = $client->getResponse();
        return $response;
    }

    protected function postRequest($uri, array $parameters, $content = null)
    {
        return $this->request('POST', $uri, $parameters, array(), array(), $content);
    }

    protected function patchRequest($uri, array $parameters, $content = null)
    {
        return $this->request('PATCH', $uri, $parameters, array(), array(), $content);
    }

    protected function putRequest($uri, array $parameters, $content = null)
    {
        return $this->request('PUT', $uri, $parameters, array(), array(), $content);
    }

    protected function getRequest($uri, array $parameters = array())
    {
        return $this->request('GET', $uri, $parameters);
    }

    protected function deleteRequest($uri, array $parameters = array())
    {
        return $this->request('DELETE', $uri, $parameters);
    }
}
<?php

namespace TestTools\Basho;

use Basho\Riak as BashoRiak;
use Basho\Riak\Command;
use Basho\Riak\Command\Object\Fetch;
use TestTools\Fixture\SelfInitializingFixtureTrait;

class Riak extends BashoRiak
{
    use SelfInitializingFixtureTrait;

    protected function getFixtureFingerprintArguments(array $arguments)
    {
        $fingerprintArguments = array();

        foreach ($arguments as $arg) {
            if (is_object($arg) && method_exists($arg, '__toString')) {
                $fingerprintArg = (string)$arg;
            } elseif ($arg instanceof Command) {
                $fingerprintArg = $arg->getMethod();

                if($arg->getLocation()) {
                    $location = $arg->getLocation()->__toString();

                    if($arg->getMethod() != 'GET') {
                        $location = substr($location, 0, strrpos($location, '_'));
                    }

                    $fingerprintArg .= $location;
                } else {
                    $fingerprintArg .= $arg->getBucket()->__toString();
                }

                if($arg->getParameters()) {
                    $fingerprintArg .= print_r($arg->getParameters(), true);
                }

                if($arg instanceof Fetch && is_object($arg->getObject())) {
                    $fingerprintArg .= print_r($arg->getEncodedData(), true);
                }
            } else {
                $fingerprintArg = $arg;
            }

            $fingerprintArguments[] = $fingerprintArg;
        }

        return $fingerprintArguments;
    }

    /**
     * Execute a Riak command
     *
     * @param Command $command
     *
     * @return Command\Response
     */
    public function execute(Command $command)
    {
        return $this->callWithFixtures('execute', func_get_args());
    }
}
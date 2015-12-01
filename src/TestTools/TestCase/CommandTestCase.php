<?php

namespace TestTools\TestCase;

use Symfony\Component\Console\Application;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Command\Command;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @package TestTools
 * @license MIT
 */
class CommandTestCase extends WebTestCase
{
    public function executeCommand(Command $commandInstance, $commandName, array $params = array())
    {
        $application = new Application();
        $application->add($commandInstance);

        if($commandInstance instanceof ContainerAwareCommand) {
            $client = $this->getClient();
            $container = $client->getContainer();
            $commandInstance->setContainer($container);
        }

        $command = $application->find($commandName);
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()) + $params);

        return $commandTester->getDisplay();
    }
}
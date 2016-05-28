<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\DropSchemaDoctrineCommand;
use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use PhpOption\Option;
use Symfony\Component\Console\Command\Command;

abstract class Base extends WebTestCase
{
    protected static function resetDatabase()
    {
        $client    = static::createClient();
        $container = $client->getContainer();
        static::runCommand($container, new DropSchemaDoctrineCommand(), 'doctrine:schema:drop', array('--force' => true));
        static::runCommand($container, new CreateSchemaDoctrineCommand(), 'doctrine:schema:create');
        static::runCommand($container, new LoadDataFixturesDoctrineCommand(), 'doctrine:fixtures:load', array('--append' => true, '--fixtures' => './src/BCRM/BackendBundle/Tests/data/fixtures/'));
    }

    /**
     * Executes a console command.
     *
     * @param ContainerInterface $container
     * @param Command            $command
     * @param string             $alias
     * @param array              $args
     */
    protected static function runCommand(ContainerInterface $container, Command $command, $alias, array $args = null)
    {
        $application = new Application(static::$kernel);
        $application->setAutoExit(false);
        $application->add($command);

        $command = $application->find($alias);
        if ($command instanceof ContainerAwareCommand) {
            $command->setContainer($container);
        }

        $commandTester = new CommandTester($command);
        $call          = array_merge(Option::fromValue($args)->getOrElse(array()), array('command' => $command->getName()));
        $commandTester->execute($call);
    }

    /**
     * Asserts that $needle is in $haystack.
     *
     * @param $needle
     * @param $haystack
     */
    protected function assertInArray($needle, $haystack)
    {
        $this->assertTrue(in_array($needle, $haystack), sprintf('Failed asserting that %s is not in array.', $needle));
    }

    /**
     * Asserts that $needle is not in $haystack.
     *
     * @param $needle
     * @param $haystack
     */
    protected function assertNotInArray($needle, $haystack)
    {
        $this->assertFalse(in_array($needle, $haystack), sprintf('Failed asserting that %s is not in array.', $needle));
    }
}

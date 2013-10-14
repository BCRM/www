<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use BCRM\BackendBundle\Entity\Event\Ticket;
use BCRM\BackendBundle\Entity\Event\Registration;
use BCRM\BackendBundle\Entity\Event\Unregistration;
use BCRM\BackendBundle\Command\SendConfirmRegistrationMailCommand;
use BCRM\BackendBundle\Command\SendConfirmUnregistrationMailCommand;
use BCRM\BackendBundle\Command\CreateTicketsCommand;
use BCRM\BackendBundle\Command\ProcessUnregistrationsCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventControllerTest extends WebTestCase
{
    /**
     * @test
     * @group functional
     */
    public function eventRegistration()
    {
        $email   = 'name@domain.com';
        $client  = static::createClient();
        $crawler = $client->request('GET', '/anmeldung');

        $form                            = $crawler->selectButton('event_register[save]')->form();
        $form['event_register[name]']    = 'John Doe';
        $form['event_register[email]']   = $email;
        $form['event_register[days]']    = 3;
        $form['event_register[arrival]'] = 'public';
        $client->submit($form);
        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->isRedirect('/anmeldung/ok'), sprintf('Unexpected redirect to %s', $response->headers->get('Location')));
        return $email;
    }

    /**
     * @test
     * @group   functional
     * @depends eventRegistration
     */
    public function confirmEventRegistration($email)
    {
        $client    = static::createClient();
        $container = $client->getContainer();

        $this->confirmRegistrationCommand($container);

        // Confirm registration key
        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $em = $container
            ->get('doctrine')
            ->getManager();

        /* @var $registration Registration */
        $registration = $em->getRepository('BCRMBackendBundle:Event\Registration')->findOneBy(array(
            'email' => $email
        ));

        // Confirm
        $client->request('GET', sprintf('/anmeldung/bestaetigen/%d/%s', $registration->getId(), $registration->getConfirmationKey()));
        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->isRedirect('/anmeldung/aktiviert'), sprintf('Unexpected redirect to %s', $response->headers->get('Location')));
        return $email;
    }

    protected function confirmRegistrationCommand(ContainerInterface $container)
    {
        $this->runCommand($container, new SendConfirmRegistrationMailCommand(), 'bcrm:registration:confirm');
    }

    protected function runCommand(ContainerInterface $container, $command, $alias)
    {
        $application = new Application();
        $application->setAutoExit(false);
        $application->add($command);

        $command = $application->find($alias);
        $command->setContainer($container);
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));
    }

    /**
     * @test
     * @group   functional
     * @depends confirmEventRegistration
     */
    public function createTickets($email)
    {
        $client    = static::createClient();
        $container = $client->getContainer();

        $this->createTicketsCommand($container);

        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $em = $container
            ->get('doctrine')
            ->getManager();

        /* @var $tickets Ticket[] */
        $tickets = $em->getRepository('BCRMBackendBundle:Event\Ticket')->findAll();
        $this->assertEquals(2, count($tickets));
        $this->assertEquals($email, $tickets[0]->getEmail());
        $this->assertEquals($email, $tickets[1]->getEmail());
        $days = array(
            $tickets[0]->getDay(),
            $tickets[1]->getDay()
        );
        $this->assertTrue(in_array(Ticket::DAY_SATURDAY, $days));
        $this->assertTrue(in_array(Ticket::DAY_SUNDAY, $days));
        $this->assertEquals(1, $tickets[0]->getEvent()->getId());
        $this->assertEquals(1, $tickets[1]->getEvent()->getId());
        return $email;
    }

    protected function createTicketsCommand(ContainerInterface $container)
    {
        $this->runCommand($container, new CreateTicketsCommand(), 'bcrm:tickets:create');
    }

    /**
     * @test
     * @group   functional
     * @depends createTickets
     */
    public function eventUnregistration($email)
    {
        $client  = static::createClient();
        $crawler = $client->request('GET', '/stornierung');

        $form                            = $crawler->selectButton('event_unregister[save]')->form();
        $form['event_unregister[email]'] = $email;
        $form['event_unregister[days]']  = 3;
        $client->submit($form);
        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->isRedirect('/stornierung/ok'), sprintf('Unexpected redirect to %s', $response->headers->get('Location')));
        return $email;
    }

    /**
     * @test
     * @group   functional
     * @depends eventUnregistration
     */
    public function confirmEventUnregistration($email)
    {
        $client    = static::createClient();
        $container = $client->getContainer();

        $this->confirmUnregistrationCommand($container);

        // Confirm registration key
        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $em = $container
            ->get('doctrine')
            ->getManager();

        /* @var $registration Registration */
        $registration = $em->getRepository('BCRMBackendBundle:Event\Unregistration')->findOneBy(array(
            'email' => $email
        ));

        // Confirm
        $client->request('GET', sprintf('/stornierung/bestaetigen/%d/%s', $registration->getId(), $registration->getConfirmationKey()));
        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->isRedirect('/stornierung/aktiviert'), sprintf('Unexpected redirect to %s', $response->headers->get('Location')));
        return $email;
    }

    protected function confirmUnregistrationCommand(ContainerInterface $container)
    {
        $this->runCommand($container, new SendConfirmUnregistrationMailCommand(), 'bcrm:unregistration:confirm');
    }

    /**
     * @test
     * @group   functional
     * @depends confirmEventUnregistration
     */
    public function removeTickets($email)
    {
        $client    = static::createClient();
        $container = $client->getContainer();

        $this->processUnregistrationsCommand($container);

        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $em = $container
            ->get('doctrine')
            ->getManager();

        /* @var $registration Registration */
        $registration = $em->getRepository('BCRMBackendBundle:Event\Registration')->findOneById(2);
        $this->assertEquals(0, $registration->getSaturday());
        $this->assertEquals(0, $registration->getSunday());

        /* @var $tickets Ticket[] */
        $tickets = $em->getRepository('BCRMBackendBundle:Event\Ticket')->findAll();
        $this->assertEquals(0, count($tickets));
        return $email;
    }

    protected function processUnregistrationsCommand(ContainerInterface $container)
    {
        $this->runCommand($container, new ProcessUnregistrationsCommand(), 'bcrm:tickets:process-unregistrations');
    }

    /**
     * @test
     * @group   functional
     * @depends removeTickets
     */
    public function thereShouldBeNoMoreTicketsThanCapacity()
    {
        $client    = static::createClient();
        $container = $client->getContainer();
        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $em = $container
            ->get('doctrine')
            ->getManager();

        $this->assertEquals(0, count($em->getRepository('BCRMBackendBundle:Event\Ticket')->findAll()));

        $event = $em->getRepository('BCRMBackendBundle:Event\Event')->findAll()[0];

        // Create confirmed registrations
        for ($i = 1; $i <= 5; $i++) {
            $email        = 'john.doe.198' . $i . '@domain.com';
            $registration = new Registration();
            $registration->setName($i);
            $registration->setEvent($event);
            $registration->setEmail($email);
            $registration->setSaturday(true);
            $registration->setConfirmed(true);
            $em->persist($registration);
        }
        $em->flush();

        $this->createTicketsCommand($container);

        $tickets = $em->getRepository('BCRMBackendBundle:Event\Ticket')->findBy(array(
            'event' => $event,
            'day'   => Ticket::DAY_SATURDAY,
        ));
        $this->assertEquals(3, count($tickets));
        $this->assertInArray('john.doe.1981@domain.com', array_map(function (Ticket $t) {
            return $t->getEmail();
        }, $tickets));

        // Unregister Tickets
        $unregistration = new Unregistration();
        $unregistration->setEvent($event);
        $unregistration->setEmail('john.doe.1981@domain.com');
        $unregistration->setConfirmed(true);
        $unregistration->setSaturday(true);
        $em->persist($unregistration);
        $em->flush();

        $this->processUnregistrationsCommand($container);

        $this->assertEquals(2, count($em->getRepository('BCRMBackendBundle:Event\Ticket')->findBy(array(
            'event' => $event,
            'day'   => Ticket::DAY_SATURDAY,
        ))));

        $this->createTicketsCommand($container);

        $tickets = $em->getRepository('BCRMBackendBundle:Event\Ticket')->findBy(array(
            'event' => $event,
            'day'   => Ticket::DAY_SATURDAY,
        ));
        $this->assertEquals(3, count($tickets));
        $this->assertNotInArray('john.doe.1981@domain.com', array_map(function (Ticket $t) {
            return $t->getEmail();
        }, $tickets));
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

    /**
     * @test
     * @group functional
     * @depends thereShouldBeNoMoreTicketsThanCapacity
     */
    public function cancelTicketLink()
    {
        $client    = static::createClient();
        $container = $client->getContainer();
        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $em     = $container
            ->get('doctrine')
            ->getManager();
        $ticket = $em->getRepository('BCRMBackendBundle:Event\Ticket')->findOneBy(array('day' => 1));
        
        // Confirm
        $crawler = $client->request('GET', sprintf('/stornierung/%d/%s', $ticket->getId(), $ticket->getCode()));
        $form                            = $crawler->selectButton('cancel_confirm')->form();
        $client->submit($form);
        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->isRedirect('/stornierung/aktiviert'), sprintf('Unexpected redirect to %s', $response->headers->get('Location')));
        
        $this->processUnregistrationsCommand($container);
        
        $this->assertEquals(2, count($em->getRepository('BCRMBackendBundle:Event\Ticket')->findAll()));
    }
}

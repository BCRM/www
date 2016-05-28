<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Tests\Functional;

use BCRM\BackendBundle\Command\CreateTicketsCommand;
use BCRM\BackendBundle\Entity\Event\Registration;
use BCRM\BackendBundle\Entity\Event\Ticket;
use BCRM\BackendBundle\Entity\Payment;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * - VIPs and Sponsors must always be given tickets.
 * - The events capacity must only be applied to the regular tickets.
 */
class VipTicketsTest extends Base
{
    /**
     * The setUpBeforeClass() and tearDownAfterClass() template methods are called before the first test of the test
     * case class is run and after the last test of the test case class is run, respectively.
     */
    public static function setUpBeforeClass()
    {
        static::resetDatabase();
    }

    /**
     * @test
     * @group   functional
     */
    public function vipTicketsShouldAlwaysBeCreated()
    {
        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $client    = static::createClient();
        $container = $client->getContainer();
        $em        = $container
            ->get('doctrine')
            ->getManager();

        $event = $em->getRepository('BCRMBackendBundle:Event\Event')->findAll()[0];

        // Create normal registrations
        $johnPays = new Payment();
        $johnPays->setTransactionId('johnpay');
        $johnPays->setMethod('cash');
        $em->persist($johnPays);
        $john = new Registration();
        $john->setUuid('john');
        $john->setName('John');
        $john->setEvent($event);
        $john->setEmail('john@domain.com');
        $john->setSaturday(true);
        $john->setPayment($johnPays);
        $em->persist($john);

        $maryPays = new Payment();
        $maryPays->setTransactionId('marypay');
        $maryPays->setMethod('cash');
        $em->persist($maryPays);
        $mary = new Registration();
        $mary->setUuid('mary');
        $mary->setName('Mary');
        $mary->setEvent($event);
        $mary->setEmail('mary@domain.com');
        $mary->setSaturday(true);
        $mary->setPayment($maryPays);
        $em->persist($mary);

        $em->flush();

        $this->createTicketsCommand($container);

        $this->assertEquals(2, count($em->getRepository('BCRMBackendBundle:Event\Ticket')->findBy(array(
            'event' => $event,
            'day'   => Ticket::DAY_SATURDAY,
        ))));

        // Create VIP registration
        $vip = new Registration();
        $vip->setUuid('VIP');
        $vip->setName('VIP');
        $vip->setEvent($event);
        $vip->setEmail('vip@domain.com');
        $vip->setSaturday(true);
        $vip->setType(Registration::TYPE_VIP);
        $em->persist($vip);

        // Create Sponsor registration
        $sponsor = new Registration();
        $sponsor->setUuid('Sponsor');
        $sponsor->setName('Sponsor');
        $sponsor->setEvent($event);
        $sponsor->setEmail('sponsor@domain.com');
        $sponsor->setSaturday(true);
        $sponsor->setType(Registration::TYPE_SPONSOR);
        $em->persist($sponsor);

        $em->flush();

        $this->createTicketsCommand($container);

        $this->assertEquals(4, count($em->getRepository('BCRMBackendBundle:Event\Ticket')->findBy(array(
            'event' => $event,
            'day'   => Ticket::DAY_SATURDAY,
        ))));
    }

    protected function createTicketsCommand(ContainerInterface $container)
    {
        $this->runCommand($container, new CreateTicketsCommand(), 'bcrm:tickets:create');
    }

    /**
     * @test
     * @depends vipTicketsShouldAlwaysBeCreated
     */
    function vipTicketsShouldNotCountForEventCapacity()
    {
        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $client    = static::createClient();
        $container = $client->getContainer();
        $em        = $container
            ->get('doctrine')
            ->getManager();

        $event = $em->getRepository('BCRMBackendBundle:Event\Event')->findAll()[0];

        // Create two other normal registrations
        $joePays = new Payment();
        $joePays->setTransactionId('joepay');
        $joePays->setMethod('cash');
        $em->persist($joePays);
        $joe = new Registration();
        $joe->setName('Joe');
        $joe->setUuid('Joe');
        $joe->setEvent($event);
        $joe->setEmail('joe@domain.com');
        $joe->setSaturday(true);
        $joe->setPayment($joePays);
        $em->persist($joe);
        $jillPays = new Payment();
        $jillPays->setTransactionId('jillpays');
        $jillPays->setMethod('cash');
        $em->persist($jillPays);
        $jill = new Registration();
        $jill->setName('Jill');
        $jill->setUuid('Jill');
        $jill->setEvent($event);
        $jill->setEmail('jill@domain.com');
        $jill->setSaturday(true);
        $jill->setPayment($jillPays);
        $em->persist($jill);
        $em->flush();
        $this->createTicketsCommand($container);
        $this->assertEquals(5, count($em->getRepository('BCRMBackendBundle:Event\Ticket')->findBy(array(
            'event' => $event,
            'day'   => Ticket::DAY_SATURDAY,
        ))));
    }

    /**
     * @test
     * @group   functional
     * @group   regression
     * @depends vipTicketsShouldNotCountForEventCapacity
     */
    public function vipTicketsShouldBeCreatedIfEventIsOverCapacity()
    {
        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $client    = static::createClient();
        $container = $client->getContainer();
        $em        = $container
            ->get('doctrine')
            ->getManager();

        $event = $em->getRepository('BCRMBackendBundle:Event\Event')->findAll()[0];
        $event->setCapacity(1);
        $em->persist($event);
        $em->flush();

        // Create Sponsor registration
        $sponsor2 = new Registration();
        $sponsor2->setName('Sponsor 2');
        $sponsor2->setUuid('Sponsor 2');
        $sponsor2->setEvent($event);
        $sponsor2->setEmail('sponsor2@domain.com');
        $sponsor2->setSaturday(true);
        $sponsor2->setType(Registration::TYPE_SPONSOR);
        $em->persist($sponsor2);

        $em->flush();

        $this->createTicketsCommand($container);

        $this->assertEquals(6, count($em->getRepository('BCRMBackendBundle:Event\Ticket')->findBy(array(
            'event' => $event,
            'day'   => Ticket::DAY_SATURDAY,
        ))));
    }
}

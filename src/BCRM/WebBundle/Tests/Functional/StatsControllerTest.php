<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Tests\Functional;

use BCRM\BackendBundle\Entity\Event\Registration;
use BCRM\BackendBundle\Entity\Event\Ticket;
use BCRM\BackendBundle\Entity\Payment;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StatsControllerTest extends Base
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
     * @group functional
     */
    public function stats()
    {
        $email = $this->createTicket(Ticket::DAY_SATURDAY);
        $this->createTicket(Ticket::DAY_SATURDAY);
        $this->createTicket(Ticket::DAY_SATURDAY, false);
        $this->createTicket(Ticket::DAY_SATURDAY, false);
        $this->createTicket(Ticket::DAY_SUNDAY, null, $email);
        $this->createTicket(Ticket::DAY_SUNDAY);
        $this->createTicket(Ticket::DAY_SUNDAY);
        $this->createTicket(Ticket::DAY_SUNDAY, false);

        $client = static::createClient();
        $client->request('GET', '/stats.json');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("application/json", $response->headers->get('Content-Type'));
        $this->assertEquals("utf-8", $response->getCharset());
        $response = json_decode($response->getContent());
        $this->assertObjectHasAttribute('stats', $response);
        $stats = $response->stats;
        $this->assertObjectHasAttribute('checkins', $stats);
        $checkins = $stats->checkins;

        $this->assertEquals(2, $checkins->sa);
        $this->assertEquals(3, $checkins->su);
        $this->assertEquals(1, $checkins->unique->sa);
        $this->assertEquals(2, $checkins->unique->su);
        $this->assertEquals(1, $checkins->unique->both);
        $this->assertEquals(2, $checkins->noshows->sa);
        $this->assertEquals(1, $checkins->noshows->su);
        $this->assertEquals($checkins->sa, $checkins->unique->sa + $checkins->unique->both);
        $this->assertEquals($checkins->su, $checkins->unique->su + $checkins->unique->both);


    }

    protected $ticketCounter = 1;

    protected function createTicket($day, $checkedIn = null, $email = null)
    {
        if ($email === null) $email = sprintf('stats.doe.198%s@domain.com', $this->ticketCounter);
        if ($checkedIn === null) $checkedIn = true;
        $client    = static::createClient();
        $container = $client->getContainer();

        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $em = $container
            ->get('doctrine')
            ->getManager();

        // Create a payment
        $txId = sha1('someid' . $email);
        $payment  = $em->getRepository('BCRMBackendBundle:Payment')->findOneByTxId($txId);
        if (!$payment) {
            $payment = new Payment();
            $payment->setTransactionId($txId);
            $payment->setMethod('somemethod');
            $em->persist($payment);
        }

        // Create a ticket
        $event  = $em->getRepository('BCRMBackendBundle:Event\Event')->findAll()[0];
        $ticket = new Ticket();
        $ticket->setEmail($email);
        $ticket->setName('John Doe');
        $ticket->setEvent($event);
        $ticket->setDay($day);
        $ticket->setCode(sprintf('STATS%d', $this->ticketCounter++));
        $ticket->setCheckedIn($checkedIn);
        $ticket->setPayment($payment);
        $em->persist($ticket);
        $em->flush();
        return $email;
    }
}

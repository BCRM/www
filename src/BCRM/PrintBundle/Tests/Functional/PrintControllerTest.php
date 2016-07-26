<?php

/**
 * @author    Markus Tacker <m@coderbyheart.com>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\PrintBundle\Tests\Functional;

use BCRM\BackendBundle\Entity\Event\Registration;
use BCRM\BackendBundle\Entity\Event\Ticket;
use BCRM\BackendBundle\Entity\Payment;
use BCRM\WebBundle\Tests\Functional\Base;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConciergeControllerTest extends Base
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
    public function listUnprintedTickets()
    {
        $client    = static::createClient();
        $container = $client->getContainer();

        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $em    = $container
            ->get('doctrine')
            ->getManager();
        $event = $em->getRepository('BCRMBackendBundle:Event\Event')->findAll()[0];

        // Create registrations
        for ($i = 0; $i < 5; $i++) {
            $payment = new Payment();
            $payment->setTransactionId('payment' . $i);
            $payment->setMethod('cash');
            $em->persist($payment);
            $registration = new Registration();
            $registration->setUuid($i);
            $registration->setEmail(sprintf('john.doe.198%d@domain.com', $i));
            $registration->setName(sprintf('John Doe %d', $i));
            $registration->setEvent($event);
            $registration->setSaturday(false);
            $registration->setSunday(true);
            $registration->setTags(sprintf('#johndoe%d', $i));
            $registration->setPayment($payment);
            $em->persist($registration);
        }

        // Create tickets
        for ($i = 0; $i < 5; $i++) {
            $checkedIn    = $i > 1;
            $sundayTicket = new Ticket();
            $sundayTicket->setEmail(sprintf('john.doe.198%d@domain.com', $i));
            $sundayTicket->setName(sprintf('John Doe %d', $i));
            $sundayTicket->setEvent($event);
            $sundayTicket->setDay(Ticket::DAY_SUNDAY);
            $sundayTicket->setCode(sprintf('PRNT%d', $i));
            $sundayTicket->setNotified(true);
            $sundayTicket->setCheckedIn($checkedIn);
            $em->persist($sundayTicket);
            $saturdayTicket = new Ticket();
            $saturdayTicket->setEmail(sprintf('john.doe.198%d@domain.com', $i));
            $saturdayTicket->setName(sprintf('John Doe %d', $i));
            $saturdayTicket->setEvent($event);
            $saturdayTicket->setDay(Ticket::DAY_SATURDAY);
            $saturdayTicket->setCode(sprintf('NOPRNT%d', $i));
            $saturdayTicket->setNotified(true);
            $saturdayTicket->setCheckedIn($checkedIn);
            $em->persist($saturdayTicket);
        }
        $em->flush();

        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'concierge',
            'PHP_AUTH_PW'   => 'letmein',
        ));
        $client->request('GET', '/api/printing/queue');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("application/json", $response->headers->get('Content-Type'));
        $this->assertEquals("utf-8", $response->getCharset());
        $queue = json_decode($response->getContent());
        $this->assertEquals(3, count($queue->items));

        $ticket = $queue->items[0];
        $this->assertObjectHasAttribute('name', $ticket);
        $this->assertObjectHasAttribute('tags', $ticket);
        $this->assertObjectHasAttribute('code', $ticket);
        $this->assertObjectHasAttribute('day', $ticket);
        $this->assertEquals('#' . strtolower(str_replace(' ', '', $ticket->name)), $ticket->tags);
        $this->assertEquals(1, preg_match('/^PRNT[0-9]+$/', $ticket->code));
        $this->assertEquals(2, $ticket->day);

        return $queue->items;
    }

    /**
     * @depends listUnprintedTickets
     * @test
     * @group   functional
     *
     * @param array $items
     */
    public function printTicket(array $items)
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'concierge',
            'PHP_AUTH_PW'   => 'letmein',
        ));

        // Print a ticket
        $client->request('PATCH', $items[0]->{'@subject'});
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // Queue should not contain item
        $client->request('GET', '/api/printing/queue');
        $response = $client->getResponse();
        $queue2   = json_decode($response->getContent());
        $this->assertFalse(in_array($items[0]->{'@subject'}, array_map(function ($item) {
            return $item->{'@subject'};
        }, $queue2->items)));

        return $items;
    }

    /**
     * @test
     * @group   functional
     * @depends printTicket
     *
     * @param array $items
     */
    public function ticketsCanBeReprinted(array $items)
    {
        $client    = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'concierge',
            'PHP_AUTH_PW'   => 'letmein',
        ));
        $container = $client->getContainer();

        // Delete printed state
        $client->request('DELETE', $items[0]->{'@subject'});
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $em = $container
            ->get('doctrine')
            ->getManager();

        /* @var $ticket Ticket */
        $ticket = $em->getRepository('BCRMBackendBundle:Event\Ticket')->findOneBy(array('code' => $items[0]->code));
        $this->assertFalse($ticket->isPrinted());
    }
}

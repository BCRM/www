<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Tests\Functional;

use BCRM\BackendBundle\Entity\Event\Registration;
use BCRM\BackendBundle\Entity\Event\Ticket;
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
     * @group current
     */
    public function stats()
    {
        $email = $this->createCheckedInTicket(Ticket::DAY_SATURDAY);
        $this->createCheckedInTicket(Ticket::DAY_SATURDAY);
        $this->createCheckedInTicket(Ticket::DAY_SUNDAY, $email);
        $this->createCheckedInTicket(Ticket::DAY_SUNDAY);
        $this->createCheckedInTicket(Ticket::DAY_SUNDAY);

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
        $this->assertEquals(1, $checkins->only_sa);
        $this->assertEquals(2, $checkins->only_su);
        $this->assertEquals(1, $checkins->both);
        $this->assertEquals($checkins->sa, $checkins->only_sa + $checkins->both);
        $this->assertEquals($checkins->su, $checkins->only_su + $checkins->both);


    }

    protected $ticketCounter = 1;

    protected function createCheckedInTicket($day, $email = null)
    {
        if ($email === null) $email = sprintf('stats.doe.198%s@domain.com', $this->ticketCounter);
        $client    = static::createClient();
        $container = $client->getContainer();

        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $em = $container
            ->get('doctrine')
            ->getManager();

        // Create a ticket
        $event  = $em->getRepository('BCRMBackendBundle:Event\Event')->findAll()[0];
        $ticket = new Ticket();
        $ticket->setEmail($email);
        $ticket->setName('John Doe');
        $ticket->setEvent($event);
        $ticket->setDay($day);
        $ticket->setCode(sprintf('STATS%d', $this->ticketCounter++));
        $ticket->setCheckedIn(true);
        $em->persist($ticket);
        $em->flush();
        return $email;
    }
}

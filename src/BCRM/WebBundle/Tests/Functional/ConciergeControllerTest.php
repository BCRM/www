<?php

/**
 * @author    Markus Tacker <m@coderbyheart.com>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Tests\Functional;

use BCRM\BackendBundle\Entity\Event\Ticket;

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
    public function eventCheckin()
    {
        $client    = static::createClient();
        $container = $client->getContainer();

        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $em = $container
            ->get('doctrine')
            ->getManager();

        // Create a ticket
        $event  = $em->getRepository('BCRMBackendBundle:Event\Event')->findAll()[0];
        $ticket = new Ticket();
        $ticket->setEmail('john.doe.1981@domain.com');
        $ticket->setName('John Doe');
        $ticket->setEvent($event);
        $ticket->setDay(Ticket::DAY_SUNDAY);
        $ticket->setCode('WOOT');
        $em->persist($ticket);
        $em->flush();

        $ticket = $em->getRepository('BCRMBackendBundle:Event\Ticket')->findOneBy(array('code' => 'WOOT'));
        $this->assertEquals(false, $ticket->isCheckedIn());

        $client   = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'concierge',
            'PHP_AUTH_PW'   => 'letmein',
        ));
        $crawler  = $client->request('GET', sprintf('/checkin/%d/%s', $ticket->getId(), $ticket->getCode()));
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // Page must contain correct ticket details
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("John Doe")')->count(),
            'Visitor name is not shown'
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("WOOT")')->count(),
            'Ticket code is not shown'
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Sonntag")')->count(),
            'Ticket day is not shown'
        );

        /* @var $ticket Ticket */
        $qb = $em->getRepository('BCRMBackendBundle:Event\Ticket')->createQueryBuilder('t');
        $qb->andWhere('t.code = :code')->setParameter('code', 'WOOT');
        $qb->andWhere('t.checkedIn IS NOT NULL');
        $ticket = $qb->getQuery()->getOneOrNullResult();

        // $this->assertEquals(true, $ticket->isCheckedIn()); // FIXME: actually it is 1, possible bug in Doctrine 
        $this->assertEquals('WOOT', $ticket->getCode());
        $em->remove($ticket);
        $em->flush();
    }

    /**
     * @test
     * @depends eventCheckin
     */
    public function doubleCheckinShouldNotWork()
    {
        $client    = static::createClient();
        $container = $client->getContainer();

        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $em = $container
            ->get('doctrine')
            ->getManager();

        // Create a ticket
        $event  = $em->getRepository('BCRMBackendBundle:Event\Event')->findAll()[0];
        $ticket = new Ticket();
        $ticket->setEmail('doublecheckin@domain.com');
        $ticket->setName('John Doe');
        $ticket->setEvent($event);
        $ticket->setDay(Ticket::DAY_SUNDAY);
        $ticket->setCode('DBLCHKN');
        $em->persist($ticket);
        $em->flush();

        /* @var $ticket Ticket */
        $ticket = $em->getRepository('BCRMBackendBundle:Event\Ticket')->findOneBy(array('code' => 'DBLCHKN'));

        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'concierge',
            'PHP_AUTH_PW'   => 'letmein',
        ));
        $client->request('GET', sprintf('/checkin/%d/%s', $ticket->getId(), $ticket->getCode()));
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // Second checkin attempt should not work
        $client->request('GET', sprintf('/checkin/%d/%s', $ticket->getId(), $ticket->getCode()));
        $response = $client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());

    }

    /**
     * @test
     * @depends eventCheckin
     */
    public function checkinsOnTheWrongDayShouldNotWork()
    {
        $client    = static::createClient();
        $container = $client->getContainer();

        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $em = $container
            ->get('doctrine')
            ->getManager();

        // Create a ticket
        $event  = $em->getRepository('BCRMBackendBundle:Event\Event')->findAll()[0];
        $ticket = new Ticket();
        $ticket->setEmail('sundaycheckin@domain.com');
        $ticket->setName('John Doe');
        $ticket->setEvent($event);
        $ticket->setDay(Ticket::DAY_SATURDAY);
        $ticket->setCode('STRDYCHKN');
        $em->persist($ticket);
        $em->flush();

        /* @var $ticket Ticket */
        $ticket = $em->getRepository('BCRMBackendBundle:Event\Ticket')->findOneBy(array('code' => 'STRDYCHKN'));

        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'concierge',
            'PHP_AUTH_PW'   => 'letmein',
        ));
        $client->request('GET', sprintf('/checkin/%d/%s', $ticket->getId(), $ticket->getCode()));
        $response = $client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * @test
     * @group   functional
     * @depends checkinsOnTheWrongDayShouldNotWork
     */
    public function theTicketSearchApiShouldWork()
    {
        $client    = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'concierge',
            'PHP_AUTH_PW'   => 'letmein',
        ));
        $container = $client->getContainer();

        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $em = $container
            ->get('doctrine')
            ->getManager();

        // Create a ticket
        $event  = $em->getRepository('BCRMBackendBundle:Event\Event')->findAll()[0];
        $ticket = new Ticket();
        $ticket->setEmail('searchticket@domain.com');
        $ticket->setName('John Doe');
        $ticket->setEvent($event);
        $ticket->setDay(Ticket::DAY_SUNDAY);
        $ticket->setCode('SRCHTCKT');
        $em->persist($ticket);
        $em->flush();

        $client->request('GET', '/api/concierge/ticketsearch?q=SRCHTCKT');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("application/json", $response->headers->get('Content-Type'));
        $this->assertEquals("utf-8", $response->getCharset());
        $result = json_decode($response->getContent());
        $this->assertEquals(1, count($result->items));

        $ticket = $result->items[0];
        $this->assertEquals('searchticket@domain.com', $ticket->email);
        $this->assertEquals(false, $ticket->checkedIn);
        $this->assertEquals(2, $ticket->day);
        $this->assertEquals('SRCHTCKT', $ticket->code);
        $this->assertTrue($ticket->id > 1);
    }
}

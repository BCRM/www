<?php
use BCRM\BackendBundle\Entity\Event\Registration;
use BCRM\BackendBundle\Entity\Event\Ticket;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

class ConciergeControllerTest extends WebTestCase
{
    /**
     * @test
     * @group functional
     */
    public function eventCheckin()
    {
        $client    = static::createClient();
        $container = $client->getContainer();

        // Confirm registration key
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
        $ticket->setDay(Ticket::DAY_SATURDAY);
        $ticket->setCode('WOOT');
        $em->persist($ticket);
        $em->flush();

        $ticket = $em->getRepository('BCRMBackendBundle:Event\Ticket')->findOneBy(array('code' => 'WOOT'));
        $this->assertEquals(false, $ticket->isCheckedIn());

        $client   = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'concierge',
            'PHP_AUTH_PW'   => 'letmein',
        ));
        $client->request('GET', sprintf('/checkin/%d/%s', $ticket->getId(), $ticket->getCode()));
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        
        /* @var $ticket Ticket */
        $ticket = $em->getRepository('BCRMBackendBundle:Event\Ticket')->findOneBy(array('code' => 'WOOT', 'checkedIn' => 1));
        // $this->assertEquals(true, $ticket->isCheckedIn()); // FIXME: actually it is 1, possible bug in Doctrine 
        $this->assertEquals('WOOT', $ticket->getCode());
    }
}

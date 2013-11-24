<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Controller;

use BCRM\BackendBundle\Entity\Event\EventRepository;
use BCRM\BackendBundle\Entity\Event\Ticket;
use BCRM\BackendBundle\Entity\Event\TicketRepository;
use BCRM\WebBundle\Content\ContentReader;
use BCRM\WebBundle\Exception\AccesDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * Stats API endpoints.
 */
class StatsController
{
    public function __construct(ContentReader $reader, EventRepository $eventRepo, TicketRepository $ticketRepo, EngineInterface $renderer)
    {
        $this->eventRepo  = $eventRepo;
        $this->ticketRepo = $ticketRepo;
        $this->reader     = $reader;
        $this->renderer   = $renderer;
    }

    /**
     * Generates the event's statistics as json.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function statsAction(Request $request)
    {
        $format = $request->get('_format');
        if ($format === 'json') {
            return $this->statsJson($request);
        }

        $response = new Response();
        $response->setTtl(60 * 5);
        $response->setPublic();
        if ($response->isNotModified($request)) {
            return $response;
        }

        $data = array(
            'sponsors' => $this->reader->getPage('Sponsoren/Index.md')
        );

        return $this->renderer->renderResponse('BCRMWebBundle:Stats:stats.html.twig', $data, $response);
    }

    /**
     * Creates the JSON used to render the stats.
     *
     * @param Request $request
     *
     * @return Response
     */
    protected function statsJson(Request $request)
    {
        $response = new Response();
        $response->setTtl(60 * 5);
        $response->setPublic();
        if ($response->isNotModified($request)) {
            return $response;
        }
        $event   = $this->eventRepo->getNextEvent()->getOrThrow(new AccesDeniedHttpException('No event.'));
        $tickets = $this->ticketRepo->getTicketsForEvent($event);

        $stats = array(
            'checkins' => array(
                'sa' => $this->getCheckinsPerDay($tickets, 1),
                'su' => $this->getCheckinsPerDay($tickets, 2),
            ),
        );

        $data = array('stats' => $stats);
        $response->setContent(json_encode($data));
        $response->setCharset('utf-8');
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    protected function getCheckinsPerDay($tickets, $day)
    {
        return array_reduce($tickets, function ($count, Ticket $ticket) use ($day) {
            return $count + ($ticket->isCheckedIn() && $ticket->getDay() == $day ? 1 : 0);
        }, 0);
    }
}

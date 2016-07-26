<?php

/**
 * @author    Markus Tacker <m@coderbyheart.com>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Controller;

use BCRM\BackendBundle\Entity\Event\Event;
use BCRM\BackendBundle\Entity\Event\EventRepository;
use BCRM\BackendBundle\Entity\Event\Registration;
use BCRM\BackendBundle\Entity\Event\Ticket;
use BCRM\BackendBundle\Entity\Event\TicketRepository;
use BCRM\BackendBundle\Entity\Event\Unregistration;
use BCRM\BackendBundle\Entity\Event\UnregistrationRepository;
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
    public function __construct(
        ContentReader $reader,
        EventRepository $eventRepo,
        TicketRepository $ticketRepo,
        UnregistrationRepository $unregistrationRepo,
        EngineInterface $renderer
    )
    {
        $this->eventRepo          = $eventRepo;
        $this->ticketRepo         = $ticketRepo;
        $this->unregistrationRepo = $unregistrationRepo;
        $this->reader             = $reader;
        $this->renderer           = $renderer;
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
        $response->setTtl(10);
        $response->setPublic();
        if ($response->isNotModified($request)) {
            return $response;
        }
        $event           = $this->eventRepo->getNextEvent()->getOrThrow(new AccesDeniedHttpException('No event.'));
        $tickets         = $this->ticketRepo->getTicketsForEvent($event);
        $unregistrations = $this->unregistrationRepo->getUnregistrationsForEvent($event);

        $stats = array(
            'checkins'        => array(
                'sa'      => $this->getCheckinsPerDay($tickets, Ticket::DAY_SATURDAY),
                'sa_hour' => $this->getCheckinsPerHour($tickets, Ticket::DAY_SATURDAY),
                'su'      => $this->getCheckinsPerDay($tickets, Ticket::DAY_SUNDAY),
                'su_hour' => $this->getCheckinsPerHour($tickets, Ticket::DAY_SUNDAY),
                'unique'  => array(
                    'sa'   => $this->getUniqueDayCheckins($tickets, Ticket::DAY_SATURDAY),
                    'su'   => $this->getUniqueDayCheckins($tickets, Ticket::DAY_SUNDAY),
                    'both' => $this->getUniqueDayCheckins($tickets, Ticket::DAY_SUNDAY, true),
                ),
                'noshows' => array(
                    'sa' => $this->getNoShows($tickets, Ticket::DAY_SATURDAY),
                    'su' => $this->getNoShows($tickets, Ticket::DAY_SUNDAY),
                )
            ),
            'unregistrations' => $this->getUnregistrationsPerDay($unregistrations)
        );

        $data = array('stats' => $stats);
        $response->setContent(json_encode($data));
        $response->setCharset('utf-8');
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Returns the number of checkins per day.
     *
     * @param Ticket[] $tickets
     * @param integer  $day
     *
     * @return mixed
     */
    protected function getCheckinsPerDay(array $tickets, $day)
    {
        return array_reduce($tickets, function ($count, Ticket $ticket) use ($day) {
            return $count + ($ticket->getType() === Registration::TYPE_NORMAL && $ticket->isCheckedIn() && $ticket->getDay() == $day ? 1 : 0);
        }, 0);
    }

    /**
     * Returns the number of attendees who have checked in only on the given day
     *
     * @param Ticket[] $tickets
     * @param integer  $day
     * @param boolean  $both
     *
     * @return integer
     */
    protected function getUniqueDayCheckins(array $tickets, $day, $both = false)
    {
        $otherDayCheckins = array_map(
            function (Ticket $ticket) {
                return $ticket->getEmail();
            },
            array_filter($tickets, function (Ticket $ticket) use ($day) {
                return
                    $ticket->getType() === Registration::TYPE_NORMAL
                    && $ticket->isCheckedIn()
                    && $ticket->getDay() != $day;
            })
        );

        return array_reduce($tickets, function ($count, Ticket $ticket) use ($otherDayCheckins, $day, $both) {
            return $count + (
            $ticket->getType() === Registration::TYPE_NORMAL
            && $ticket->isCheckedIn()
            && $ticket->getDay() == $day
            && ($both ? in_array($ticket->getEmail(), $otherDayCheckins) : !in_array($ticket->getEmail(), $otherDayCheckins))
                ? 1 : 0);
        }, 0);
    }

    protected function getNoShows(array $tickets, $day)
    {
        return array_reduce($tickets, function ($count, Ticket $ticket) use ($day) {
            return $count + (
            $ticket->getType() === Registration::TYPE_NORMAL
            && !$ticket->isCheckedIn()
            && $ticket->getPayment() !== null
            && $ticket->getDay() == $day
                ? 1 : 0);
        }, 0);
    }

    /**
     * Returns the number of checkins per hour summarized for every 10 minutes.
     *
     * @param Ticket[] $tickets
     * @param integer  $day
     *
     * @return array
     */
    protected function getCheckinsPerHour(array $tickets, $day)
    {
        $hourCount = array();
        foreach ($tickets as $ticket) {
            if ($ticket->getDay() != $day
                || !$ticket->isCheckedIn()
            ) {
                continue;
            }
            $slot = min(max(800, intval(substr(ltrim($ticket->getCheckinTime()->format('Hi'), '0'), 0, -1)) * 10), 1600);
            if (!isset($hourCount[$slot])) {
                $hourCount[$slot] = 1;
            } else {
                $hourCount[$slot]++;
            }
        }
        asort($hourCount);
        return $hourCount;
    }

    /**
     * Returns the number of checkins per day.
     *
     * @param Unregistration[] $unregistrations
     *
     * @return array
     */
    protected function getUnregistrationsPerDay($unregistrations)
    {
        $data = array();
        $sort = array();
        foreach ($unregistrations as $unregistration) {
            $slot = $unregistration->getCreated()->format('j.m.');
            if (!isset($data[$slot])) {
                $data[$slot] = array('sa' => 0, 'su' => 0);
                $sort[] = $unregistration->getCreated()->format('Y-m-d');
            }
            if ($unregistration->getSaturday()) {
                $data[$slot]['sa'] += 1;
            }
            if ($unregistration->getSunday()) {
                $data[$slot]['su'] += 1;
            }
        }
        array_multisort($sort, SORT_ASC, $data);
        return $data;
    }
}

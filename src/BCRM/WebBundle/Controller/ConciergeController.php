<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Controller;

use BCRM\BackendBundle\Entity\Event\Event;
use BCRM\BackendBundle\Entity\Event\EventRepository;
use BCRM\BackendBundle\Entity\Event\Ticket;
use BCRM\BackendBundle\Entity\Event\TicketRepository;
use BCRM\BackendBundle\Service\Concierge\CheckinCommand;
use BCRM\WebBundle\Content\ContentReader;
use BCRM\WebBundle\Exception\AccesDeniedHttpException;
use BCRM\WebBundle\Exception\BadRequestException;
use Carbon\Carbon;
use LiteCQRS\Bus\CommandBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Dashboard for the event concierge.
 */
class ConciergeController
{
    public function __construct(EventRepository $eventRepo, TicketRepository $ticketRepo, CommandBus $commandBus)
    {
        $this->eventRepo  = $eventRepo;
        $this->ticketRepo = $ticketRepo;
        $this->commandBus = $commandBus;
    }

    /**
     * Dashboard index
     *
     * @Template()
     */
    public function indexAction()
    {
        /* @var $event Event */
        $event = $this->eventRepo->getNextEvent()->getOrThrow(new AccesDeniedHttpException('No event.'));

        $stats = array();
        foreach (array(Ticket::DAY_SATURDAY, Ticket::DAY_SUNDAY) as $day) {
            $stats[$day] = array(
                'tickets'  => $this->ticketRepo->getTicketCountForEvent($event, $day),
                'checkins' => $this->ticketRepo->getCheckinCountForEvent($event, $day),
            );
        }
        $data = array('stats' => $stats);
        return $data;
    }
}

<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\PrintBundle\Controller;

use BCRM\BackendBundle\Entity\Event\Event;
use BCRM\BackendBundle\Entity\Event\EventRepository;
use BCRM\BackendBundle\Entity\Event\RegistrationRepository;
use BCRM\BackendBundle\Entity\Event\Ticket;
use BCRM\BackendBundle\Entity\Event\TicketRepository;
use BCRM\PrintBundle\Exception\AccesDeniedHttpException;
use BCRM\PrintBundle\Exception\NotFoundHttpException;
use Carbon\Carbon;
use LiteCQRS\Bus\CommandBus;
use LiteCQRS\Plugin\CRUD\Model\Commands\UpdateResourceCommand;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * Dashboard for the event concierge.
 */
class PrintingController
{
    public function __construct(EventRepository $eventRepo, RegistrationRepository $registrationRepo, TicketRepository $ticketRepo, CommandBus $commandBus, RouterInterface $router, $schemeAndHost)
    {
        $this->eventRepo        = $eventRepo;
        $this->ticketRepo       = $ticketRepo;
        $this->registrationRepo = $registrationRepo;
        $this->commandBus       = $commandBus;
        $this->router           = $router;
        $this->schemeAndHost    = trim($schemeAndHost, '/ ');
    }

    /**
     * List unprinted tickets.
     */
    public function queueAction()
    {
        /* @var $event Event */
        $event = $this->eventRepo->getNextEvent()->getOrThrow(new AccesDeniedHttpException('No event.'));

        // Do not allow checkins on the wrong day
        $now   = new Carbon();
        $start = Carbon::createFromTimestamp($event->getStart()->getTimestamp());
        $day   =
            $start->setTime(0, 0, 0);
        $end   = clone $start;
        $end->setTime(23, 59, 59);
        $day   = $now->between($start, $end) ? Ticket::DAY_SATURDAY : Ticket::DAY_SUNDAY;
        $items = array();
        foreach ($this->ticketRepo->getUnprintedTickets($event, $day) as $ticket) {
            $registration = $this->registrationRepo->getRegistrationForEmail($event, $ticket->getEmail())->get();
            $type         = null;
            $items[]      = array(
                '@context' => 'http://barcamp-rheinmain.de/jsonld/Ticket',
                '@subject' => $this->schemeAndHost . $this->router->generate('bcrmprint_ticket', array('id' => $ticket->getId(), 'code' => $ticket->getCode())),
                'name'     => $ticket->getName(),
                'twitter'  => $registration->getTwitter(),
                'code'     => $ticket->getCode(),
                'day'      => $ticket->getDay(),
                'tags'     => $registration->getTags(),
            );
        }
        $data     = array('items' => $items);
        $response = new Response(json_encode($data));
        $response->setCharset('utf-8');
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Mark the given ticket as printed.
     *
     * @param Request $request
     * @param int     $id
     * @param string  $code
     *
     * @return Response
     */
    public function printAction(Request $request, $id, $code)
    {
        $ticket               = $this->ticketRepo->getTicketByIdAndCode($id, $code)->getOrThrow(new NotFoundHttpException('Ticket not found.'));
        $updateCommand        = new UpdateResourceCommand();
        $updateCommand->class = '\BCRM\BackendBundle\Entity\Event\Ticket';
        $updateCommand->id    = $id;
        $updateCommand->data  = array('printed' => $request->getMethod() === 'PATCH');
        $this->commandBus->handle($updateCommand);
        $response = new Response(json_encode(array('status' => 'OK')));
        $response->setCharset('utf-8');
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}

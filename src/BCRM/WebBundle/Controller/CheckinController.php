<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2015 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Controller;

use BCRM\BackendBundle\Entity\Event\Event;
use BCRM\BackendBundle\Entity\Event\EventRepository;
use BCRM\BackendBundle\Entity\Event\RegistrationRepository;
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
 * Manages event checkins.
 */
class CheckinController
{
    public function __construct(
        EventRepository $eventRepo,
        TicketRepository $ticketRepo,
        RegistrationRepository $registrationRepo,
        CommandBus $commandBus
    )
    {
        $this->ticketRepo       = $ticketRepo;
        $this->eventRepo        = $eventRepo;
        $this->registrationRepo = $registrationRepo;
        $this->commandBus       = $commandBus;
    }

    /**
     * @param Request $request
     * @param         $id
     * @param         $code
     *
     * @return array|RedirectResponse
     * @Template()
     */
    public function checkinAction(Request $request, $id, $code)
    {
        /* @var $ticket Ticket */
        $ticket = $this->ticketRepo->getTicketByIdAndCode($id, $code)->getOrThrow(new NotFoundHttpException('Unknown ticket.'));

        // Do not allow double checkins
        if ($ticket->isCheckedIn()) {
            throw new BadRequestException('Already checked in!');
        }

        // Do not allow checkins on the wrong day
        /* @var $event Event */
        $event = $this->eventRepo->getNextEvent()->getOrThrow(new AccesDeniedHttpException('No event.'));
        $now   = new Carbon();
        $start = Carbon::createFromTimestamp($event->getStart()->getTimestamp());
        $start->setTime(0, 0, 0);
        if ($ticket->isSunday()) {
            $start->modify('+1day');
        }
        $end = clone $start;
        $end->setTime(23, 59, 59);
        if (!$now->between($start, $end)) {
            throw new BadRequestException('Wrong day!');
        }

        $registrationOption = $this->registrationRepo->getRegistrationForEmail($ticket->getEvent(), $ticket->getEmail());


        // Record checkin
        $command         = new CheckinCommand();
        $command->ticket = $ticket;
        $this->commandBus->handle($command);

        return array(
            'ticket'       => $ticket,
            'registration' => $registrationOption->getOrElse(null),
        );
    }
}

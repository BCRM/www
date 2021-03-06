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
use BCRM\BackendBundle\Service\Concierge\CheckinCommand;
use BCRM\BackendBundle\Service\Concierge\PayRegistrationConciergeCommand;
use BCRM\BackendBundle\Service\Event\CreateTicketCommand;
use BCRM\BackendBundle\Service\Event\RegisterCommand;
use BCRM\WebBundle\Content\ContentReader;
use BCRM\WebBundle\Exception\AccesDeniedHttpException;
use BCRM\WebBundle\Exception\BadRequestException;
use BCRM\WebBundle\Form\TicketType;
use Carbon\Carbon;
use LiteCQRS\Bus\CommandBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Security\Core\Util\SecureRandom;

/**
 * Dashboard for the event concierge.
 */
class ConciergeController
{
    public function __construct(
        EventRepository $eventRepo,
        TicketRepository $ticketRepo,
        CommandBus $commandBus,
        FormFactoryInterface $formFactory,
        RouterInterface $router)
    {
        $this->eventRepo   = $eventRepo;
        $this->ticketRepo  = $ticketRepo;
        $this->commandBus  = $commandBus;
        $this->formFactory = $formFactory;
        $this->router      = $router;
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

    /**
     * Concierges must be able to create Tickets manually.
     *
     * @Template()
     */
    public function createTicketAction(Request $request)
    {
        $event        = $this->eventRepo->getNextEvent()->getOrThrow(new AccessDeniedHttpException('No event.'));
        $registration = new Registration();
        $registration->setEvent($event);
        $generator = new SecureRandom();
        $registration->setUuid(sha1($generator->nextBytes(16)));
        $form = $this->formFactory->create(new TicketType(), $registration, array('action' => $request->getPathInfo()));
        $form->handleRequest($request);
        if ($form->isValid()) {
            /* @var Registration $formData */
            $formData                  = $form->getData();
            $registerCommand           = new RegisterCommand();
            $registerCommand->event    = $event;
            $registerCommand->email    = $formData->getEmail();
            $registerCommand->name     = $formData->getName();
            $registerCommand->twitter  = $formData->getTwitter();
            $registerCommand->saturday = $formData->getSaturday();
            $registerCommand->sunday   = $formData->getSunday();
            $registerCommand->tags     = $formData->getTags();
            $registerCommand->type     = $formData->getType();
            $registerCommand->uuid     = $formData->getUuid();
            $registerCommand->payment  = 'concierge';
            $this->commandBus->handle($registerCommand);

            // Create tickets
            /* @var FlashBagInterface $fb */
            $fb = $request->getSession()->getFlashBag();
            foreach (
                array(
                    Ticket::DAY_SATURDAY => $formData->getSaturday(),
                    Ticket::DAY_SUNDAY   => $formData->getSunday(),
                ) as $day => $want) {
                if (!$want) continue;
                $ticketCommand               = new CreateTicketCommand();
                $ticketCommand->day          = $day;
                $ticketCommand->event        = $event;
                $ticketCommand->registration = $registration;
                $this->commandBus->handle($ticketCommand);
                $fb->add(
                    'info',
                    sprintf(
                        'Ticket für %s / %s angelegt.',
                        $registration->getEmail(),
                        $day == Ticket::DAY_SATURDAY ? 'Samstag' : 'Sonntag'
                    )
                );
            }

            // Create payment
            $paymentCommand = new PayRegistrationConciergeCommand();
            $paymentCommand->uuid = $registerCommand->uuid;
            $this->commandBus->handle($paymentCommand);

            return new RedirectResponse($this->router->generate('bcrmweb_concierge_index'));
        }
        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Allows the concierge to checkin attendees which did not bring their ticket,
     *
     * @Template()
     */
    public function manualCheckinAction()
    {
        $event = $this->eventRepo->getNextEvent()->getOrThrow(new AccessDeniedHttpException('No event.'));
        return array();
    }

    /**
     * Search for tickets
     *
     * @param Request $request
     */
    public function searchTicketAction(Request $request)
    {
        $event = $this->eventRepo->getNextEvent()->getOrThrow(new AccessDeniedHttpException('No event.'));
        // Do not allow checkins on the wrong day
        /* @var $event Event */
        $now   = new Carbon();
        $start = Carbon::createFromTimestamp($event->getStart()->getTimestamp());
        $start->setTime(0, 0, 0);
        $end = clone $start;
        $end->setTime(23, 59, 59);
        $day = $now->between($start, $end) ? Ticket::DAY_SATURDAY : Ticket::DAY_SUNDAY;

        $tickets  = array_map(function (Ticket $ticket) {
            return array(
                'code'      => $ticket->getCode(),
                'email'     => $ticket->getEmail(),
                'name'      => $ticket->getName(),
                'checkedIn' => $ticket->isCheckedIn(),
                'day'       => $ticket->getDay(),
                'id'        => $ticket->getId(),
            );
        }, $this->ticketRepo->searchTickets($event, $day, $request->get('q')));
        $data     = array('items' => $tickets);
        $response = new Response(json_encode($data));
        $response->setCharset('utf-8');
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}

<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Controller;

use BCRM\BackendBundle\Entity\Event\EventRepository;
use BCRM\BackendBundle\Entity\Event\Registration;
use BCRM\BackendBundle\Entity\Event\RegistrationRepository;
use BCRM\BackendBundle\Entity\Event\Ticket;
use BCRM\BackendBundle\Entity\Event\TicketRepository;
use BCRM\BackendBundle\Entity\Event\UnregistrationRepository;
use BCRM\BackendBundle\Service\Event\ConfirmUnregistrationCommand;
use BCRM\BackendBundle\Service\Event\RegisterCommand;
use BCRM\BackendBundle\Service\Event\UnregisterCommand;
use BCRM\WebBundle\Content\ContentReader;
use BCRM\WebBundle\Form\EventRegisterModel;
use BCRM\WebBundle\Form\EventRegisterType;
use BCRM\WebBundle\Form\EventUnregisterType;
use Carbon\Carbon;
use Dothiv\Bundle\MoneyFormatBundle\Service\MoneyFormatServiceInterface;
use LiteCQRS\Bus\CommandBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Util\SecureRandom;

/**
 * Manages event registrations.
 */
class EventController
{
    /**
     * @var ContentReader
     */
    private $reader;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \LiteCQRS\Bus\CommandBus
     */
    private $commandBus;

    /**
     * @var \BCRM\BackendBundle\Entity\Event\RegistrationRepository
     */
    private $registrationRepo;

    /**
     * @var \BCRM\BackendBundle\Entity\Event\EventRepository
     */
    private $eventRepo;

    /**
     * @var \BCRM\BackendBundle\Entity\Event\UnregistrationRepository
     */
    private $unregistrationRepo;

    /**
     * @var \BCRM\BackendBundle\Entity\Event\TicketRepository
     */
    private $ticketRepo;

    /**
     * @var MoneyFormatServiceInterface
     */
    private $moneyFormat;

    public function __construct(
        ContentReader $reader,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        CommandBus $commandBus,
        EventRepository $eventRepo,
        RegistrationRepository $registrationRepo,
        UnregistrationRepository $unregistrationRepo,
        TicketRepository $ticketRepo,
        MoneyFormatServiceInterface $moneyFormat)
    {
        $this->reader             = $reader;
        $this->formFactory        = $formFactory;
        $this->router             = $router;
        $this->commandBus         = $commandBus;
        $this->eventRepo          = $eventRepo;
        $this->registrationRepo   = $registrationRepo;
        $this->unregistrationRepo = $unregistrationRepo;
        $this->ticketRepo         = $ticketRepo;
        $this->moneyFormat        = $moneyFormat;
    }

    /**
     * @param Request $request
     *
     * @Template()
     */
    public function registerAction(Request $request)
    {
        $event = $this->eventRepo->getNextEvent()->getOrThrow(new AccessDeniedHttpException('No event.'));
        if (Carbon::createFromTimestamp($event->getRegistrationEnd()->getTimestamp())->isPast()) {
            throw new AccessDeniedHttpException('Registration not possible.');
        }
        $model          = new EventRegisterModel();
        $model->payment = 'paypal';
        if ($request->getSession()->has('registration')) {
            $model = $request->getSession()->get('registration');
        }
        $model->event = $event;
        $form         = $this->formFactory->create('event_register', $model,
            array(
                'action'            => $request->getPathInfo(),
                'validation_groups' => array('registration')
            )
        );
        $form->handleRequest($request);
        if ($form->isValid()) {
            $request->getSession()->set('registration', $form->getData());
            return new RedirectResponse($this->router->generate('bcrmweb_registration_review'));
        }
        return array(
            'sponsors'             => $this->reader->getPage('Sponsoren/Index.md'),
            'content'              => $this->reader->getPage('Registrierung/Intro.md'),
            'event'                => $event,
            'pricePerDayFormatted' => $this->moneyFormat->format($event->getPrice() / 100, 'de'),
            'form'                 => $form->createView(),
        );
    }

    /**
     * @param Request $request
     *
     * @Template()
     */
    public function registerReviewAction(Request $request)
    {
        $event = $this->eventRepo->getNextEvent()->getOrThrow(new AccessDeniedHttpException('No event.'));
        if (Carbon::createFromTimestamp($event->getRegistrationEnd()->getTimestamp())->isPast()) {
            throw new AccessDeniedHttpException('Registration not possible.');
        }
        /** @var EventRegisterModel $model */
        $model        = $request->getSession()->get('registration');
        $model->event = $event;
        $form         = $this->formFactory->create('event_register_review', $model,
            array(
                'action'            => $request->getPathInfo(),
                'validation_groups' => array('review')
            )
        );
        $form->handleRequest($request);
        if ($form->isValid()) {
            /* @var EventRegisterModel $formData */
            $formData                 = $form->getData();
            $command                  = new RegisterCommand();
            $command->event           = $event;
            $command->email           = $formData->email;
            $command->name            = $formData->name;
            $command->twitter         = $formData->twitter;
            $command->saturday        = $formData->wantsSaturday();
            $command->sunday          = $formData->wantsSunday();
            $command->food            = $formData->food;
            $command->participantList = $formData->participantList;
            $command->tags            = $formData->tags;
            $command->donation        = $formData->getDonation();
            $command->payment         = $formData->payment;
            $generator                = new SecureRandom();
            $command->uuid            = sha1($generator->nextBytes(16));
            $this->commandBus->handle($command);
            $request->getSession()->remove('registration');
            return new RedirectResponse($this->router->generate('bcrmweb_registration_ok'));
        }
        // TODO: Move to service
        $ticketPrice = ($model->days === 3 ? 2 : 1) * $event->getPrice();
        $orderTotal  = $model->getDonation() + $ticketPrice;
        $fees        = 0;
        if ($model->payment === 'paypal') {
            //  Mit Paypal (zzgl. 1,9% + 0,35 Cent)
            $fees = ceil($orderTotal * 0.019) + 35;
        } else {
            // Mit barzahlen.de (zzgl. 3,0% + 0,35 Cent)
            $fees = ceil($orderTotal * 0.03) + 35;
        }
        $total = $model->getDonation() + $ticketPrice + $fees;
        return array(
            'sponsors'             => $this->reader->getPage('Sponsoren/Index.md'),
            'event'                => $event,
            'pricePerDayFormatted' => $this->moneyFormat->format($event->getPrice() / 100, 'de'),
            'ticketPriceFormatted' => $this->moneyFormat->format($ticketPrice / 100, 'de'),
            'donationFormatted'    => $this->moneyFormat->format($model->getDonation() / 100, 'de'),
            'orderTotalFormatted'  => $this->moneyFormat->format($orderTotal / 100, 'de'),
            'feesFormatted'        => $this->moneyFormat->format($fees / 100, 'de'),
            'totalFormatted'       => $this->moneyFormat->format($total / 100, 'de'),
            'form'                 => $form->createView(),
            'registration'         => $model,
        );
    }

    /**
     * @param Request $request
     *
     * @Template()
     *
     * @return array
     */
    public function registerPaymentAction($id, Request $request)
    {
        $event = $this->eventRepo->getNextEvent()->getOrThrow(new AccessDeniedHttpException('No event.'));
        if (Carbon::createFromTimestamp($event->getRegistrationEnd()->getTimestamp())->isPast()) {
            throw new AccessDeniedHttpException('Registration not possible.');
        }
        $registrationOptional = $this->registrationRepo->getRegistrationByUuid($id);
        if ($registrationOptional->isEmpty()) {
            throw new NotFoundHttpException('Unknown registration.');
        }
        /** @var Registration $registration */
        $registration = $registrationOptional->get();
        $tickets      = $this->ticketRepo->getTicketsForEmail($event, $registration->getEmail());
        $numTickets   = count($tickets);
        // TODO: Move to service
        $registeredDays = 0;
        if ($registration->getSaturday()) {
            $registeredDays += 1;
        }
        if ($registration->getSunday()) {
            $registeredDays += 1;
        }
        $ticketPrice = $numTickets * $event->getPrice();
        $orderTotal  = $registration->getDonation() + $ticketPrice;
        $fees        = 0;
        if ($registration->getPaymentMethod() === 'paypal') {
            //  Mit Paypal (zzgl. 1,9% + 0,35 Cent)
            $fees = ceil($orderTotal * 0.019) + 35;
        } else {
            // Mit barzahlen.de (zzgl. 3,0% + 0,35 Cent)
            $fees = ceil($orderTotal * 0.03) + 35;
        }
        $total = $registration->getDonation() + $ticketPrice + $fees;

        /** @var EventRegisterModel $model */
        return array(
            'sponsors'     => $this->reader->getPage('Sponsoren/Index.md'),
            'event'        => $event,
            'registration' => $registration,
            'total'        => $total,
            'tickets'      => $tickets,
            'partialOrder' => $registeredDays !== $numTickets,
            'days'         => $registeredDays
        );
    }

    /**
     * @param Request $request
     *
     * @Template()
     */
    public function unregisterAction(Request $request)
    {
        $form = $this->formFactory->create(new EventUnregisterType());
        $form->handleRequest($request);
        if ($form->isValid()) {
            /* @var EventRegisterModel $formData */
            $formData          = $form->getData();
            $command           = new UnregisterCommand();
            $command->event    = $this->eventRepo->getNextEvent()->getOrThrow(new AccessDeniedHttpException('No event.'));
            $command->email    = $formData->email;
            $command->saturday = $formData->wantsSaturday();
            $command->sunday   = $formData->wantsSunday();
            $this->commandBus->handle($command);
            return new RedirectResponse($this->router->generate('bcrmweb_unregistration_ok'));
        }
        return array(
            'sponsors' => $this->reader->getPage('Sponsoren/Index.md'),
            'form'     => $form->createView(),
        );
    }

    public function confirmUnregistrationAction($id, $key)
    {
        $unregistration = $this->unregistrationRepo->getUnregistrationByIdAndKey($id, $key);
        if ($unregistration->isEmpty()) {
            throw new NotFoundHttpException('Unknown unregistration.');
        }
        $command                 = new ConfirmUnregistrationCommand();
        $command->unregistration = $unregistration->get();
        $this->commandBus->handle($command);
        return new RedirectResponse($this->router->generate('bcrmweb_unregistration_confirmed'));
    }

    /**
     * @param Request $request
     * @param         $id
     * @param         $code
     *
     * @return array|RedirectResponse
     * @Template()
     */
    public function cancelTicketAction(Request $request, $id, $code)
    {
        /* @var $ticket Ticket */
        $ticket = $this->ticketRepo->getTicketByIdAndCode($id, $code)->getOrThrow(new NotFoundHttpException('Unknown ticket.'));

        if ($request->isMethod('POST')) {
            $command            = new UnregisterCommand();
            $command->event     = $this->eventRepo->getNextEvent()->getOrThrow(new AccessDeniedHttpException('No event.'));
            $command->email     = $ticket->getEmail();
            $command->saturday  = $ticket->isSaturday();
            $command->sunday    = $ticket->isSunday();
            $command->confirmed = true;
            $this->commandBus->handle($command);
            return new RedirectResponse($this->router->generate('bcrmweb_unregistration_confirmed'));
        }

        return array(
            'ticket'   => $ticket,
            'sponsors' => $this->reader->getPage('Sponsoren/Index.md'),
        );
    }

    /**
     * @return Response
     * @Template()
     */
    public function participantListAction()
    {
        $event        = $this->eventRepo->getNextEvent()->getOrThrow(new AccessDeniedHttpException('No event.'));
        $participants = $this->registrationRepo->getParticipantList($event);
        return array(
            'participants' => $participants,
            'sponsors'     => $this->reader->getPage('Sponsoren/Index.md'),
        );
    }
}

<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2015 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Controller;

use BCRM\BackendBundle\Entity\Event\EventRepository;
use BCRM\BackendBundle\Entity\Event\RegistrationRepository;
use BCRM\BackendBundle\Entity\Event\Ticket;
use BCRM\BackendBundle\Entity\Event\TicketRepository;
use BCRM\BackendBundle\Entity\Event\UnregistrationRepository;
use BCRM\BackendBundle\Service\Event\ConfirmRegistrationCommand;
use BCRM\BackendBundle\Service\Event\ConfirmUnregistrationCommand;
use BCRM\BackendBundle\Service\Event\RegisterCommand;
use BCRM\BackendBundle\Service\Event\UnregisterCommand;
use BCRM\WebBundle\Content\ContentReader;
use BCRM\WebBundle\Form\EventRegisterModel;
use BCRM\WebBundle\Form\EventRegisterType;
use BCRM\WebBundle\Form\EventUnregisterType;
use Carbon\Carbon;
use LiteCQRS\Bus\CommandBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

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

    public function __construct(ContentReader $reader, FormFactoryInterface $formFactory, RouterInterface $router, CommandBus $commandBus, EventRepository $eventRepo, RegistrationRepository $registrationRepo, UnregistrationRepository $unregistrationRepo, TicketRepository $ticketRepo)
    {
        $this->reader             = $reader;
        $this->formFactory        = $formFactory;
        $this->router             = $router;
        $this->commandBus         = $commandBus;
        $this->eventRepo          = $eventRepo;
        $this->registrationRepo   = $registrationRepo;
        $this->unregistrationRepo = $unregistrationRepo;
        $this->ticketRepo         = $ticketRepo;
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
        $form = $this->formFactory->create(new EventRegisterType(), null, array('action' => $request->getPathInfo()));
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
            $command->arrival         = $formData->arrival;
            $command->food            = $formData->food;
            $command->participantList = $formData->participantList;
            $command->tags            = $formData->tags;
            $this->commandBus->handle($command);
            return new RedirectResponse($this->router->generate('bcrmweb_registration_ok'));
        }
        return array(
            'sponsors' => $this->reader->getPage('Sponsoren/Index.md'),
            'form'     => $form->createView(),
        );
    }

    public function confirmRegistrationAction($id, $key)
    {
        $registration = $this->registrationRepo->getRegistrationByIdAndKey($id, $key);
        if ($registration->isEmpty()) {
            throw new NotFoundHttpException('Unknown registration.');
        }
        $command               = new ConfirmRegistrationCommand();
        $command->registration = $registration->get();
        $this->commandBus->handle($command);
        return new RedirectResponse($this->router->generate('bcrmweb_registration_confirmed'));
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
            'sponsors' => $this->reader->getPage('Sponsoren/Index.md'),
        );
    }
}

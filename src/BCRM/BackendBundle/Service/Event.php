<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service;

use BCRM\BackendBundle\Entity\Event\RegistrationRepository;
use BCRM\BackendBundle\Entity\Event\TicketRepository;
use BCRM\BackendBundle\Entity\Event\UnregistrationRepository;
use BCRM\BackendBundle\Event\Event\TicketDeletedEvent;
use BCRM\BackendBundle\Event\Event\TicketMailSentEvent;
use BCRM\BackendBundle\Service\Event\ConfirmUnregistrationCommand;
use BCRM\BackendBundle\Service\Event\CreateTicketCommand;
use BCRM\BackendBundle\Service\Event\RegisterCommand;
use BCRM\BackendBundle\Service\Event\SendRegistrationConfirmationMailCommand;
use BCRM\BackendBundle\Service\Event\SendTicketMailCommand;
use BCRM\BackendBundle\Service\Event\SendUnregistrationConfirmationMailCommand;
use BCRM\BackendBundle\Service\Event\UnregisterCommand;
use BCRM\BackendBundle\Service\Event\UnregisterTicketCommand;
use BCRM\BackendBundle\Service\Mail\SendTemplateMailCommand;
use BCRM\BackendBundle\Service\Event\ConfirmRegistrationCommand;
use LiteCQRS\Bus\CommandBus;
use LiteCQRS\Bus\EventMessageBus;
use LiteCQRS\Plugin\CRUD\Model\Commands\CreateResourceCommand;
use LiteCQRS\Plugin\CRUD\Model\Commands\DeleteResourceCommand;
use LiteCQRS\Plugin\CRUD\Model\Commands\UpdateResourceCommand;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Endroid\QrCode\QrCode;

class Event
{
    /**
     * @var \LiteCQRS\Bus\CommandBus
     */
    private $commandBus;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \BCRM\BackendBundle\Entity\Event\RegistrationRepository
     */
    private $registrationRepo;

    /**
     * @var \LiteCQRS\Bus\EventMessageBus
     */
    private $eventMessageBus;

    /**
     * @var \BCRM\BackendBundle\Entity\Event\UnregistrationRepository
     */
    private $unregistrationRepo;

    /**
     * @var \BCRM\BackendBundle\Entity\Event\TicketRepository
     */
    private $ticketRepo;

    /**
     * @param CommandBus      $commandBus
     * @param RouterInterface $router
     */
    public function __construct(CommandBus $commandBus, EventMessageBus $eventMessageBus, RouterInterface $router, RegistrationRepository $registrationRepo, UnregistrationRepository $unregistrationRepo, TicketRepository $ticketRepo)
    {
        $this->commandBus         = $commandBus;
        $this->eventMessageBus    = $eventMessageBus;
        $this->router             = $router;
        $this->registrationRepo   = $registrationRepo;
        $this->unregistrationRepo = $unregistrationRepo;
        $this->ticketRepo         = $ticketRepo;
    }

    public function register(RegisterCommand $command)
    {
        $createRegistrationCommand        = new CreateResourceCommand();
        $createRegistrationCommand->class = '\BCRM\BackendBundle\Entity\Event\Registration';
        $createRegistrationCommand->data  = array(
            'event'           => $command->event,
            'email'           => $command->email,
            'name'            => $command->name,
            'twitter'         => $command->twitter,
            'saturday'        => $command->saturday,
            'sunday'          => $command->sunday,
            'arrival'         => $command->arrival,
            'food'            => $command->food,
            'tags'            => $command->tags,
            'type'            => $command->type,
            'participantList' => $command->participantList,
            'confirmed'       => $command->confirmed
        );
        $this->commandBus->handle($createRegistrationCommand);
    }

    public function sendRegistrationConfirmationMail(SendRegistrationConfirmationMailCommand $command)
    {
        $sr                   = new SecureRandom();
        $key                  = sha1($sr->nextBytes(256), false);
        $updateCommand        = new UpdateResourceCommand();
        $updateCommand->class = '\BCRM\BackendBundle\Entity\Event\Registration';
        $updateCommand->id    = $command->registration->getId();
        $updateCommand->data  = array('confirmationKey' => $key);
        $this->commandBus->handle($updateCommand);

        $emailCommand               = new SendTemplateMailCommand();
        $emailCommand->email        = $command->registration->getEmail();
        $emailCommand->template     = 'RegistrationConfirmation';
        $emailCommand->templateData = array(
            'registration'      => $command->registration,
            'confirmation_link' => rtrim($command->schemeAndHost, '/') . $this->router->generate('bcrm_registration_confirm', array('id' => $command->registration->getId(), 'key' => $key))
        );
        $this->commandBus->handle($emailCommand);
    }

    public function confirmRegistration(ConfirmRegistrationCommand $command)
    {
        $updateCommand        = new UpdateResourceCommand();
        $updateCommand->class = '\BCRM\BackendBundle\Entity\Event\Registration';
        $updateCommand->id    = $command->registration->getId();
        $updateCommand->data  = array('confirmed' => 1);
        $this->commandBus->handle($updateCommand);
    }

    public function createTicket(CreateTicketCommand $command)
    {
        $sr   = new SecureRandom();
        $code = '';
        $max  = 6;
        while (strlen($code) < $max) {
            $seq = preg_replace('/[^A-Z0-9]/', '', $sr->nextBytes(256));
            for ($i = 0; $i < strlen($seq) && strlen($code) < $max; $i++) {
                $code .= $seq[$i];
            }
        }
        $createCommand        = new CreateResourceCommand();
        $createCommand->class = '\BCRM\BackendBundle\Entity\Event\Ticket';
        $createCommand->data  = array(
            'event' => $command->event,
            'email' => $command->registration->getEmail(),
            'name'  => $command->registration->getName(),
            'day'   => $command->day,
            'type'  => $command->registration->getType(),
            'code'  => $code,
        );
        $this->commandBus->handle($createCommand);
    }

    public function sendTicketMail(SendTicketMailCommand $command)
    {
        $qrCode = new QrCode();
        $qrCode->setText(
            rtrim($command->schemeAndHost, '/') . $this->router->generate(
                'bcrmweb_event_checkin',
                array('id' => $command->ticket->getId(), 'code' => $command->ticket->getCode())
            )
        );

        $qrCode->setSize(300);
        $qrCode->setPadding(10);
        $qrfile = tempnam(sys_get_temp_dir(), 'qrcode-') . '.png';
        $qrCode->render($qrfile);

        $emailCommand               = new SendTemplateMailCommand();
        $emailCommand->email        = $command->ticket->getEmail();
        $emailCommand->template     = 'Ticket';
        $emailCommand->templateData = array(
            'ticket'      => $command->ticket,
            'event'       => $command->event,
            'cancel_link' => rtrim($command->schemeAndHost, '/') . $this->router->generate(
                    'bcrmweb_event_cancel_ticket',
                    array('id' => $command->ticket->getId(), 'code' => $command->ticket->getCode())
                )
        );
        $emailCommand->image        = $qrfile;
        $emailCommand->format       = 'text/html';
        $this->commandBus->handle($emailCommand);

        $event         = new TicketMailSentEvent();
        $event->ticket = $command->ticket;
        $this->eventMessageBus->publish($event);
    }

    public function unregister(UnregisterCommand $command)
    {
        $createUnregistrationCommand        = new CreateResourceCommand();
        $createUnregistrationCommand->class = '\BCRM\BackendBundle\Entity\Event\Unregistration';
        $createUnregistrationCommand->data  = array(
            'event'     => $command->event,
            'email'     => $command->email,
            'saturday'  => $command->saturday,
            'sunday'    => $command->sunday,
            'confirmed' => $command->confirmed,
        );
        $this->commandBus->handle($createUnregistrationCommand);
    }

    public function sendUnregistrationConfirmationMail(SendUnregistrationConfirmationMailCommand $command)
    {
        $sr                   = new SecureRandom();
        $key                  = sha1($sr->nextBytes(256), false);
        $updateCommand        = new UpdateResourceCommand();
        $updateCommand->class = '\BCRM\BackendBundle\Entity\Event\Unregistration';
        $updateCommand->id    = $command->unregistration->getId();
        $updateCommand->data  = array('confirmationKey' => $key);
        $this->commandBus->handle($updateCommand);

        $emailCommand               = new SendTemplateMailCommand();
        $emailCommand->email        = $command->unregistration->getEmail();
        $emailCommand->template     = 'UnregistrationConfirmation';
        $emailCommand->templateData = array(
            'unregistration'    => $command->unregistration,
            'confirmation_link' => rtrim($command->schemeAndHost, '/') . $this->router->generate('bcrm_unregistration_confirm', array('id' => $command->unregistration->getId(), 'key' => $key))
        );
        $this->commandBus->handle($emailCommand);
    }

    public function confirmUnregistration(ConfirmUnregistrationCommand $command)
    {
        $updateCommand        = new UpdateResourceCommand();
        $updateCommand->class = '\BCRM\BackendBundle\Entity\Event\Unregistration';
        $updateCommand->id    = $command->unregistration->getId();
        $updateCommand->data  = array('confirmed' => 1);
        $this->commandBus->handle($updateCommand);
    }

    public function unregisterTicket(UnregisterTicketCommand $command)
    {
        // Create a new registration matching the unregistration
        $registration = $this->registrationRepo->getRegistrationForEmail($command->event, $command->unregistration->getEmail());
        if ($registration->isDefined()) {
            $r                = $registration->get();
            $registrationData = array(
                'event'     => $command->event,
                'email'     => $command->unregistration->getEmail(),
                'name'      => $r->getName(),
                'twitter'   => $r->getTwitter(),
                'arrival'   => $r->getArrival(),
                'food'      => $r->getFood(),
                'tags'      => $r->getTags(),
                'confirmed' => 1,
                'saturday'  => $r->getSaturday(),
                'sunday'    => $r->getSunday(),
            );
            if ($command->unregistration->getSaturday()) {
                $registrationData['saturday'] = false;
            }
            if ($command->unregistration->getSunday()) {
                $registrationData['sunday'] = false;
            }
            $createRegistrationCommand        = new CreateResourceCommand();
            $createRegistrationCommand->class = '\BCRM\BackendBundle\Entity\Event\Registration';
            $createRegistrationCommand->data  = $registrationData;
            $this->commandBus->handle($createRegistrationCommand);
        }

        // Delete tickets
        foreach ($this->ticketRepo->getTicketsForEmail($command->event, $command->unregistration->getEmail()) as $ticket) {
            if (
                ($ticket->isSaturday() && $command->unregistration->getSaturday())
                || ($ticket->isSunday() && $command->unregistration->getSunday())
            ) {
                $deleteTicketCommand        = new DeleteResourceCommand();
                $deleteTicketCommand->class = '\BCRM\BackendBundle\Entity\Event\Ticket';
                $deleteTicketCommand->id    = $ticket->getId();
                $this->commandBus->handle($deleteTicketCommand);

                $event         = new TicketDeletedEvent();
                $event->ticket = $ticket;
                $this->eventMessageBus->publish($event);
            }
        }

        // Mark unregistration as processed
        $updateCommand        = new UpdateResourceCommand();
        $updateCommand->class = '\BCRM\BackendBundle\Entity\Event\Unregistration';
        $updateCommand->id    = $command->unregistration->getId();
        $updateCommand->data  = array('processed' => true);
        $this->commandBus->handle($updateCommand);
    }
}

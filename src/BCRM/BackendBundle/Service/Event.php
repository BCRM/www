<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2015 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service;

use BCRM\BackendBundle\Entity\Event\Registration;
use BCRM\BackendBundle\Entity\Event\RegistrationRepository;
use BCRM\BackendBundle\Entity\Event\TicketRepository;
use BCRM\BackendBundle\Entity\Event\UnregistrationRepository;
use BCRM\BackendBundle\Event\Event\TicketDeletedEvent;
use BCRM\BackendBundle\Event\Event\TicketMailSentEvent;
use BCRM\BackendBundle\Event\Payment\PaymentVerifiedEvent;
use BCRM\BackendBundle\Event\Payment\RegistrationPaidEvent;
use BCRM\BackendBundle\Service\Event\ConfirmUnregistrationCommand;
use BCRM\BackendBundle\Service\Event\CreateTicketCommand;
use BCRM\BackendBundle\Service\Event\RegisterCommand;
use BCRM\BackendBundle\Service\Event\SendPaymentNotificationMailCommand;
use BCRM\BackendBundle\Service\Event\SendTicketMailCommand;
use BCRM\BackendBundle\Service\Event\SendUnregistrationConfirmationMailCommand;
use BCRM\BackendBundle\Service\Event\UnregisterCommand;
use BCRM\BackendBundle\Service\Event\UnregisterTicketCommand;
use BCRM\BackendBundle\Service\Mail\SendTemplateMailCommand;
use BCRM\BackendBundle\Service\Payment\PayRegistrationCommand;
use LiteCQRS\Bus\CommandBus;
use LiteCQRS\Bus\EventMessageBus;
use LiteCQRS\Plugin\CRUD\Model\Commands\CreateResourceCommand;
use LiteCQRS\Plugin\CRUD\Model\Commands\DeleteResourceCommand;
use LiteCQRS\Plugin\CRUD\Model\Commands\UpdateResourceCommand;
use PhpOption\Option;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Endroid\QrCode\QrCode;

class Event implements LoggerAwareInterface
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
     * @var LoggerInterface
     */
    private $logger;

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

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     *
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
            'food'            => $command->food,
            'tags'            => $command->tags,
            'type'            => $command->type,
            'participantList' => $command->participantList,
            'uuid'            => $command->uuid,
            'paymentMethod'   => $command->payment,
            'donation'        => (int)$command->donation
        );
        $this->commandBus->handle($createRegistrationCommand);
    }

    public function sendPaymentNotificationMail(SendPaymentNotificationMailCommand $command)
    {
        $updateCommand        = new UpdateResourceCommand();
        $updateCommand->class = '\BCRM\BackendBundle\Entity\Event\Registration';
        $updateCommand->id    = $command->registration->getId();
        $updateCommand->data  = array('paymentNotified' => new \DateTime());
        $this->commandBus->handle($updateCommand);

        $emailCommand               = new SendTemplateMailCommand();
        $emailCommand->email        = $command->registration->getEmail();
        $emailCommand->template     = 'RegistrationPayment';
        $emailCommand->templateData = array(
            'registration' => $command->registration,
            'payment_link' => rtrim($command->schemeAndHost, '/') . $this->router->generate('bcrmweb_registration_payment', array('id' => $command->registration->getUuid()))
        );
        $this->commandBus->handle($emailCommand);
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
        $registrationOptional = $this->registrationRepo->getRegistrationForEmail($command->event, $command->ticket->getEmail());
        if ($registrationOptional->isDefined()) {
            $emailCommand->templateData['registration'] = $registrationOptional->get();
        }
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
            /** @var Registration $r */
            $r                = $registration->get();
            $registrationData = array(
                'event'         => $command->event,
                'email'         => $command->unregistration->getEmail(),
                'name'          => $r->getName(),
                'twitter'       => $r->getTwitter(),
                'food'          => $r->getFood(),
                'tags'          => $r->getTags(),
                'confirmed'     => 1,
                'saturday'      => $r->getSaturday(),
                'sunday'        => $r->getSunday(),
                'uuid'          => $r->getUuid(),
                'donation'      => $r->getDonation(),
                'payment'       => $r->getPayment(),
                'paymentMethod' => $r->getPaymentMethod(),
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

    /**
     * Once a payment has been verified, find the registration it belongs to and mark it as paid
     * so the tickets can be assigned.
     *
     * @param PaymentVerifiedEvent $event
     */
    public function onPaymentVerified(PaymentVerifiedEvent $event)
    {
        $payment              = $event->payment;
        $registrationOptional = $this->registrationRepo->findByUuid($payment->getPayload()->get('item_number'));
        if ($registrationOptional->isEmpty()) {
            Option::fromValue($this->logger)->map(function (LoggerInterface $logger) use ($payment) {
                $logger->alert(sprintf('No registration found with uuid "%s"', $payment->getPayload()->get('item_number')), array($payment));
            });
            return;
        }
        $registration = $registrationOptional->get();

        $command               = new PayRegistrationCommand();
        $command->registration = $registration;
        $command->payment      = $payment;
        $this->payRegistration($command);
    }

    public function payRegistration(PayRegistrationCommand $command)
    {
        $updateCommand        = new UpdateResourceCommand();
        $updateCommand->class = '\BCRM\BackendBundle\Entity\Event\Registration';
        $updateCommand->id    = $command->registration->getId();
        $updateCommand->data  = array('payment' => $command->payment);
        $this->commandBus->handle($updateCommand);

        $event               = new RegistrationPaidEvent();
        $event->registration = $command->registration;
        $event->payment      = $command->payment;
        $this->eventMessageBus->publish($event);
    }
}

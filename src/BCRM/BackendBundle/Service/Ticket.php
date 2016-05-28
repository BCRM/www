<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service;

use BCRM\BackendBundle\Entity\Event\TicketRepository;
use BCRM\BackendBundle\Event\Event\TicketDeletedEvent;
use BCRM\BackendBundle\Event\Event\TicketMailSentEvent;
use BCRM\BackendBundle\Event\Payment\RegistrationPaidEvent;
use BCRM\BackendBundle\Service\Mail\SendTemplateMailCommand;
use LiteCQRS\Bus\CommandBus;
use LiteCQRS\Plugin\CRUD\Model\Commands\UpdateResourceCommand;
use PhpOption\Option;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Ticket implements LoggerAwareInterface
{
    /**
     * @var \LiteCQRS\Bus\CommandBus
     */
    private $commandBus;

    /**
     * @var TicketRepository
     */
    private $ticketRepo;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CommandBus       $commandBus
     * @param TicketRepository $ticketRepo
     */
    public function __construct(CommandBus $commandBus, TicketRepository $ticketRepo)
    {
        $this->commandBus = $commandBus;
        $this->ticketRepo = $ticketRepo;
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

    public function onTicketMailSent(TicketMailSentEvent $event)
    {
        $updateCommand        = new UpdateResourceCommand();
        $updateCommand->class = '\BCRM\BackendBundle\Entity\Event\Ticket';
        $updateCommand->id    = $event->ticket->getId();
        $updateCommand->data  = array('notified' => '1');
        $this->commandBus->handle($updateCommand);
    }

    public function onTicketDeleted(TicketDeletedEvent $event)
    {
        $emailCommand               = new SendTemplateMailCommand();
        $emailCommand->email        = $event->ticket->getEmail();
        $emailCommand->template     = 'TicketDelete';
        $emailCommand->templateData = array(
            'ticket' => $event->ticket,
        );
        $this->commandBus->handle($emailCommand);
    }

    /**
     * Register payment on tickets
     *
     * @param RegistrationPaidEvent $event
     */
    public function onRegistrationPaid(RegistrationPaidEvent $event)
    {
        $payment      = $event->payment;
        $registration = $event->registration;
        Option::fromValue($this->logger)->map(function (LoggerInterface $logger) use ($registration, $payment) {
            $logger->alert(sprintf('Registration "%s" has been paid with payment "%s".', $registration->getId(), $payment->getId()));
        });
        foreach ($this->ticketRepo->getTicketsForEmail(
            $registration->getEvent(),
            $registration->getEmail()
        ) as $ticket) {
            $updateCommand        = new UpdateResourceCommand();
            $updateCommand->class = '\BCRM\BackendBundle\Entity\Event\Ticket';
            $updateCommand->id    = $ticket->getId();
            $updateCommand->data  = array('payment' => $payment);
            $this->commandBus->handle($updateCommand);
            Option::fromValue($this->logger)->map(function (LoggerInterface $logger) use ($payment, $ticket) {
                $logger->alert(sprintf('Assigning payment "%s" to ticket "%s"', $payment->getId(), $ticket->getCode()));
            });
        }
    }
}

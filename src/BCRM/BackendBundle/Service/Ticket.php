<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2015 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service;

use BCRM\BackendBundle\Entity\Event\TicketRepository;
use BCRM\BackendBundle\Event\Event\TicketDeletedEvent;
use BCRM\BackendBundle\Event\Event\TicketMailSentEvent;
use BCRM\BackendBundle\Event\Payment\RegistrationPaidEvent;
use BCRM\BackendBundle\Service\Mail\SendTemplateMailCommand;
use LiteCQRS\Bus\CommandBus;
use LiteCQRS\Plugin\CRUD\Model\Commands\UpdateResourceCommand;

class Ticket
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
     * @param CommandBus       $commandBus
     * @param TicketRepository $ticketRepo
     */
    public function __construct(CommandBus $commandBus, TicketRepository $ticketRepo)
    {
        $this->commandBus = $commandBus;
        $this->ticketRepo = $ticketRepo;
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
        foreach ($this->ticketRepo->getTicketsForEmail(
            $event->registration->getEvent(),
            $event->registration->getEmail()
        ) as $ticket) {
            $updateCommand        = new UpdateResourceCommand();
            $updateCommand->class = '\BCRM\BackendBundle\Entity\Event\Ticket';
            $updateCommand->id    = $ticket->getId();
            $updateCommand->data  = array('payment' => $event->payment);
            $this->commandBus->handle($updateCommand);
        }
    }
}

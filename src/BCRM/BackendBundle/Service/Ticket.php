<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2015 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service;

use BCRM\BackendBundle\Entity\Event\RegistrationRepository;
use BCRM\BackendBundle\Event\Event\TicketDeletedEvent;
use BCRM\BackendBundle\Event\Event\TicketMailSentEvent;
use BCRM\BackendBundle\Service\Event\CreateTicketCommand;
use BCRM\BackendBundle\Service\Event\RegisterCommand;
use BCRM\BackendBundle\Service\Event\SendRegistrationConfirmationMailCommand;
use BCRM\BackendBundle\Service\Event\SendTicketMailCommand;
use BCRM\BackendBundle\Service\Mail\SendTemplateMailCommand;
use BCRM\BackendBundle\Service\Event\ConfirmRegistrationCommand;
use LiteCQRS\Bus\CommandBus;
use LiteCQRS\Bus\EventMessageBus;
use LiteCQRS\Plugin\CRUD\Model\Commands\CreateResourceCommand;
use LiteCQRS\Plugin\CRUD\Model\Commands\UpdateResourceCommand;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Util\SecureRandom;

class Ticket
{
    /**
     * @var \LiteCQRS\Bus\CommandBus
     */
    private $commandBus;

    /**
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
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
}

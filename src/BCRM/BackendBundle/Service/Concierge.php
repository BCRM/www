<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2015 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service;

use BCRM\BackendBundle\Event\Concierge\CheckedInEvent;
use BCRM\BackendBundle\Service\Concierge\CheckinCommand;
use Endroid\QrCode\QrCode;
use LiteCQRS\Bus\CommandBus;
use LiteCQRS\Bus\EventMessageBus;
use LiteCQRS\Plugin\CRUD\Model\Commands\UpdateResourceCommand;
use Symfony\Component\Routing\RouterInterface;

class Concierge
{
    /**
     * @var \LiteCQRS\Bus\CommandBus
     */
    private $commandBus;

    /**
     * @var \LiteCQRS\Bus\EventMessageBus
     */
    private $eventMessageBus;

    /**
     * @param CommandBus      $commandBus
     * @param RouterInterface $router
     */
    public function __construct(CommandBus $commandBus, EventMessageBus $eventMessageBus)
    {
        $this->commandBus      = $commandBus;
        $this->eventMessageBus = $eventMessageBus;
    }

    public function checkin(CheckinCommand $command)
    {
        $updateCommand        = new UpdateResourceCommand();
        $updateCommand->class = '\BCRM\BackendBundle\Entity\Event\Ticket';
        $updateCommand->id    = $command->ticket->getId();
        $updateCommand->data  = array('checkedIn' => new \DateTime());
        $this->commandBus->handle($updateCommand);

        $event         = new CheckedInEvent();
        $event->ticket = $command->ticket;
        $this->eventMessageBus->publish($event);
    }
}

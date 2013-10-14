<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Command;

use BCRM\BackendBundle\Entity\Event\Event;
use BCRM\BackendBundle\Exception\CommandException;
use BCRM\BackendBundle\Service\Event\SendTicketMailCommand;
use Doctrine\Common\Util\Debug;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendTicketNotificationCommand extends ContainerAwareCommand
{
    /**
     * @var OutputInterface
     */
    private $output;

    protected function configure()
    {
        $this
            ->setName('bcrm:tickets:notify')
            ->setDescription('Send ticket notifications');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        /** @var \BCRM\BackendBundle\Entity\Event\RegistrationRepository $registrationRepo */
        /** @var \BCRM\BackendBundle\Entity\Event\EventRepository $eventRepo */
        $eventRepo = $this->getContainer()->get('bcrm.backend.repo.event');
        $event     = $eventRepo->getNextEvent()->getOrThrow(new CommandException('No event.'));
        $this->sendNewTicketNotificationsFor($event);
    }

    protected function sendNewTicketNotificationsFor(Event $event)
    {
        /** @var \BCRM\BackendBundle\Entity\Event\TicketRepository $repo */
        $repo = $this->getContainer()->get('bcrm.backend.repo.ticket');
        /** @var \LiteCQRS\Bus\CommandBus $commandBus */
        $commandBus = $this->getContainer()->get('command_bus');
        foreach ($repo->getNewTickets($event) as $ticket) {
            if ($this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $this->output->writeln(sprintf('Sending ticket notification for %s', $ticket));
            }
            $command                = new SendTicketMailCommand();
            $command->ticket        = $ticket;
            $command->event         = $event;
            $command->schemeAndHost = $this->getContainer()->getParameter('scheme_and_host');
            $commandBus->handle($command);
        }
    }
}

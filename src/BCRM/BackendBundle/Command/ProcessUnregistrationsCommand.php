<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Command;

use BCRM\BackendBundle\Entity\Event\Event;
use BCRM\BackendBundle\Entity\Event\Registration;
use BCRM\BackendBundle\Entity\Event\Ticket;
use BCRM\BackendBundle\Exception\CommandException;
use BCRM\BackendBundle\Service\Event\CreateTicketCommand;
use BCRM\BackendBundle\Service\Event\UnregisterTicketCommand;
use Doctrine\Common\Util\Debug;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessUnregistrationsCommand extends ContainerAwareCommand
{
    /**
     * @var OutputInterface
     */
    private $output;

    protected function configure()
    {
        $this
            ->setName('bcrm:tickets:process-unregistrations')
            ->setDescription('Remove unregistered tickets');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        /** @var \BCRM\BackendBundle\Entity\Event\UnregistrationRepository $unRepo */
        /** @var \BCRM\BackendBundle\Entity\Event\EventRepository $eventRepo */
        $eventRepo  = $this->getContainer()->get('bcrm.backend.repo.event');
        $unRepo     = $this->getContainer()->get('bcrm.backend.repo.unregistration');
        $event      = $eventRepo->getNextEvent()->getOrThrow(new CommandException('No event.'));
        $commandBus = $this->getContainer()->get('command_bus');
        foreach ($unRepo->getUnprocessedUnregistrations($event) as $unregistration) {
            if ($this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $this->output->writeln(sprintf('Processing unregistration %s', $unregistration));
            }
            $command                 = new UnregisterTicketCommand();
            $command->unregistration = $unregistration;
            $command->event          = $event;
            $commandBus->handle($command);
        }
    }

}

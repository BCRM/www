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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTicketsCommand extends ContainerAwareCommand
{
    /**
     * @var OutputInterface
     */
    private $output;

    protected function configure()
    {
        $this
            ->setName('bcrm:tickets:create')
            ->setDescription('Create tickets for the newly registered');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        /* @var \BCRM\BackendBundle\Entity\Event\EventRepository $eventRepo */
        /* @var \BCRM\BackendBundle\Entity\Event\RegistrationRepository $registrationRepo */
        $eventRepo = $this->getContainer()->get('bcrm.backend.repo.event');
        $registrationRepo = $this->getContainer()->get('bcrm.backend.repo.registration');
        $event     = $eventRepo->getNextEvent()->getOrThrow(new CommandException('No event.'));
        foreach (array(Ticket::DAY_SATURDAY, Ticket::DAY_SUNDAY) as $day) {
            // Regular tickets
            $capacity = $eventRepo->getCapacity($event, $day);
            if ($capacity > 0) {
                foreach ($registrationRepo->getNextRegistrations($event, $day, $capacity) as $registration) {
                    $this->createTicketForRegistration($event, $registration, $day);
                }
            }
            // VIP-Tickets
            foreach ($registrationRepo->getNextVipRegistrations($event, $day) as $registration) {
                $this->createTicketForRegistration($event, $registration, $day);
            }
        }
    }

    protected function createTicketForRegistration(Event $event, Registration $registration, $day)
    {
        $commandBus = $this->getContainer()->get('command_bus');
        if ($this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $this->output->writeln(sprintf('Creating day %d ticket for registration %s', $day, $registration));
        }
        $command               = new CreateTicketCommand();
        $command->registration = $registration;
        $command->day          = $day;
        $command->event        = $event;
        $commandBus->handle($command);
    }
}

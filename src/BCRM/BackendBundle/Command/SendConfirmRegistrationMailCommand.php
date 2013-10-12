<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Command;

use BCRM\BackendBundle\Service\Event\SendRegistrationConfirmationMailCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendConfirmRegistrationMailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bcrm:registration:confirm')
            ->setDescription('Send registration confirmation emails');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \BCRM\BackendBundle\Entity\Event\RegistrationRepository $repo */
        $repo = $this->getContainer()->get('bcrm.backend.repo.registration');
        /** @var \LiteCQRS\Bus\CommandBus $commandBus */
        $commandBus = $this->getContainer()->get('command_bus');
        foreach ($repo->getNewRegistrations() as $registration) {
            if ($output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) $output->writeln($registration->getEmail());
            $command                = new SendRegistrationConfirmationMailCommand();
            $command->registration  = $registration;
            $command->schemeAndHost = $this->getContainer()->getParameter('scheme_and_host');
            $commandBus->handle($command);
        }
    }
}

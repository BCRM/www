<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Command;

use BCRM\BackendBundle\Service\Event\SendPaymentNotificationMailCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendPayRegistrationMailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bcrm:registration:pay')
            ->setDescription('Send registration payment emails');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \BCRM\BackendBundle\Entity\Event\RegistrationRepository $repo */
        $repo = $this->getContainer()->get('bcrm.backend.repo.registration');
        /** @var \LiteCQRS\Bus\CommandBus $commandBus */
        $commandBus = $this->getContainer()->get('command_bus');
        foreach ($repo->getToPay() as $registration) {
            if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln(sprintf('Sending payment notification mail for %s', $registration));
            }
            $command                = new SendPaymentNotificationMailCommand();
            $command->registration  = $registration;
            $command->schemeAndHost = $this->getContainer()->getParameter('scheme_and_host');
            $commandBus->handle($command);
        }
    }
}

<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2015 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Command;

use BCRM\BackendBundle\Service\Event\SendUnregistrationConfirmationMailCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendConfirmUnregistrationMailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bcrm:unregistration:confirm')
            ->setDescription('Send unregistration confirmation emails');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \BCRM\BackendBundle\Entity\Event\UnregistrationRepository $repo */
        $repo = $this->getContainer()->get('bcrm.backend.repo.unregistration');
        /** @var \LiteCQRS\Bus\CommandBus $commandBus */
        $commandBus = $this->getContainer()->get('command_bus');
        foreach ($repo->getNewUnregistrations() as $unregistration) {
            if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln(sprintf('Sending unregistration confirmation mail for %s', $unregistration));
            }
            $command                 = new SendUnregistrationConfirmationMailCommand();
            $command->unregistration = $unregistration;
            $command->schemeAndHost  = $this->getContainer()->getParameter('scheme_and_host');
            $commandBus->handle($command);
        }
    }
}

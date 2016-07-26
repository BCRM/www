<?php

/**
 * @author    Markus Tacker <m@coderbyheart.com>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Command;

use BCRM\BackendBundle\Service\Payment\CheckPaymentCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessPaymentsCommand extends ContainerAwareCommand
{
    /**
     * @var OutputInterface
     */
    private $output;

    protected function configure()
    {
        $this
            ->setName('bcrm:payments:process')
            ->setDescription('Process new payments');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        /** @var \BCRM\BackendBundle\Entity\PaymentRepository $paymentRepo */
        $paymentRepo = $this->getContainer()->get('bcrm.backend.repo.payment');
        $commandBus  = $this->getContainer()->get('command_bus');
        foreach ($paymentRepo->getUnchecked() as $payment) {
            if ($this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $this->output->writeln(sprintf('Checking payment %s', $payment));
            }
            $command          = new CheckPaymentCommand();
            $command->payment = $payment;
            $command->sandbox = $this->getContainer()->getParameter('paypal_sandbox');
            $commandBus->handle($command);
        }
    }
}

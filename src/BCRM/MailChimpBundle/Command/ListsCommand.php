<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\MailChimpBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListsCommand extends ContainerAwareCommand
{
    /**
     * @var OutputInterface
     */
    private $output;

    protected function configure()
    {
        $this
            ->setName('bcrm:mailchimp:lists')
            ->setDescription('List the available mailchimp lists');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var \BCRM\MailChimpBundle\MailChimp\Api $mailchimp */
        $mailchimp = $this->getContainer()->get('bcrm.mailchimp');
        $output->writeln('Available lists in our mailchimp account:');
        foreach($mailchimp->listsList() as $list) {
            $output->writeln(sprintf('%s (%s)', $list->name, $list->id));
        }

    }
}

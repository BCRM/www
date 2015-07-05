<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2015 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MailChimpListsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bcrm:mailchimp:lists')
            ->setDescription('List the available mailchimp lists');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var \Coderbyheart\MailChimpBundle\MailChimp\Api $mailchimp */
        $mailchimp = $this->getContainer()->get('mailchimp');
        $output->writeln('Available lists in our mailchimp account:');
        foreach ($mailchimp->listsList()->data as $list) {
            $output->writeln(sprintf('%s (%s)', $list->name, $list->id));
        }
    }
}

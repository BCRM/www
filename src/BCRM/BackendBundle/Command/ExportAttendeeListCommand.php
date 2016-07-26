<?php

/**
 * @author    Markus Tacker <m@coderbyheart.com>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Command;

use BCRM\BackendBundle\Entity\Event\EventRepository;
use BCRM\BackendBundle\Entity\Event\Ticket;
use BCRM\BackendBundle\Entity\Event\TicketRepository;
use BCRM\BackendBundle\Exception\BadMethodCallException;
use BCRM\BackendBundle\Exception\CommandException;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Exports the next events attendees as CSV.
 *
 * @package BCRM\BackendBundle\Command
 */
class ExportAttendeeListCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bcrm:event:export-attendees')
            ->setDescription('Exports the next events attendees as CSV.')
            ->addArgument('output', InputArgument::REQUIRED, 'Name of the file to export to');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var TicketRepository $ticketRepo */
        /* @var EventRepository $eventRepo */
        $eventRepo  = $this->getContainer()->get('bcrm.backend.repo.event');
        $ticketRepo = $this->getContainer()->get('bcrm.backend.repo.ticket');

        $tickets = array();
        $sort1   = array();
        $sort2   = array();
        foreach ($ticketRepo->getTicketsForEvent($eventRepo->getNextEvent()->getOrThrow(
            new BadMethodCallException('No event.')
        )) as $ticket) {
            /* @var Ticket $ticket */
            $tickets[] = array(
                $ticket->getDay(),
                $ticket->getCode(),
                $ticket->getName(),
                $ticket->getEmail(),
                $ticket->getLabel()
            );
            $sort1[]   = $ticket->getDay();
            $sort2[]   = $ticket->getCode();
        }

        array_multisort($sort1, SORT_ASC, $sort2, SORT_ASC, SORT_STRING, $tickets);

        $fp = fopen($input->getArgument('output'), 'w+');
        fputcsv($fp, array(
            'Tag',
            'Code',
            'Name',
            'E-Mail',
            'Typ',
        ));
        foreach ($tickets as $ticket) {
            fputcsv($fp, $ticket);
        }
        fclose($fp);
    }
}

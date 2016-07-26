<?php

/**
 * @author    Markus Tacker <m@coderbyheart.com>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Command;

use BCRM\BackendBundle\Entity\Event\EventRepository;
use BCRM\BackendBundle\Entity\Event\TicketRepository;
use BCRM\BackendBundle\Exception\CommandException;
use Coderbyheart\MailChimpBundle\Exception\BadMethodCallException;
use Coderbyheart\MailChimpBundle\MailChimp\Api as MailChimpApi;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MailChimpUpdateParticipantsListCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bcrm:mailchimp:update-participants-list')
            ->setDescription('Updates the given list to reflect the participants for the given event')
            ->addArgument('list', InputArgument::REQUIRED, 'MailChimp list identifier');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var MailChimpApi $mailchimp */
        /* @var TicketRepository $ticketRepo */
        /* @var EventRepository $eventRepo */
        $mailchimp = $this->getContainer()->get('mailchimp');

        // There is no "clear subscribers" api endpoint, so we have to batch unsubscribe the non-participants

        $output->writeln('Fetching subscribers …');
        $subscribers = new ArrayCollection();
        $page        = 0;
        do {
            $result = $mailchimp->listsMembers(
                array(
                    'id'   => $input->getArgument('list'),
                    'opts' => array(
                        'start' => $page++
                    )
                )
            );
            foreach ($result->data as $subscriber) {
                $subscribers->add(strtolower($subscriber->email));
            }
        } while (count($result->data) > 0);
        $output->writeln(sprintf("%d subscribers in list.", $subscribers->count()));

        $eventRepo    = $this->getContainer()->get('bcrm.backend.repo.event');
        $ticketRepo   = $this->getContainer()->get('bcrm.backend.repo.ticket');
        $participants = new ArrayCollection();
        foreach ($ticketRepo->getTicketsForEvent($eventRepo->getNextEvent()->getOrThrow(
            new BadMethodCallException('No event.')
        )) as $ticket) {
            if ($participants->contains(strtolower($ticket->getEmail()))) continue;
            $participants->add(strtolower($ticket->getEmail()));
        }

        // Unsubscribe former participants
        $unsubscribe = new ArrayCollection(array_diff($subscribers->toArray(), $participants->toArray()));
        $output->writeln(sprintf('Unsubscribing %d participants.', $unsubscribe->count()));
        $result = $mailchimp->listsBatch_unsubscribe(
            array(
                'id'            => $input->getArgument('list'),
                'batch'         => $this->toBatch($unsubscribe, false),
                'delete_member' => true,
                'send_goodbye'  => false,
            )
        );
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
            $output->writeln(print_r($result, true));
        }
        if ($result->error_count > 0) {
            throw new CommandException(sprintf('Failed to unsubscribe %d participants!', $result->error_count));
        }

        // Subscribe new participiants
        $newSubcsribers = new ArrayCollection(array_diff($participants->toArray(), $subscribers->toArray()));
        $output->writeln(sprintf('Subscribing %d new participants.', $newSubcsribers->count()));
        $result = $mailchimp->listsBatch_subscribe(
            array(
                'id'           => $input->getArgument('list'),
                'batch'        => $this->toBatch($newSubcsribers),
                'double_optin' => false
            )
        );
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
            $output->writeln(print_r($result, true));
        }
    }

    /**
     * @param ArrayCollection $emails
     *
     * @return string[]
     */
    protected function toBatch(ArrayCollection $emails, $deep = true)
    {
        return $emails->map(function ($email) use($deep) {
            return array(
                'email'      => $deep ? array('email' => $email) : $email,
                'email_type' => 'html'
            );
        })->toArray();
    }
}

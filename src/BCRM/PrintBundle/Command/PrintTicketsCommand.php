<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\PrintBundle\Command;

use BCRM\BackendBundle\Entity\Event\Event;
use BCRM\BackendBundle\Entity\Event\Registration;
use BCRM\BackendBundle\Entity\Event\Ticket;
use BCRM\BackendBundle\Exception\CommandException;
use BCRM\BackendBundle\Service\Event\CreateTicketCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PrintTicketsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bcrm:tickets:print')
            ->setDescription('Print tickets')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'URL to the printing queue.')
            ->addOption('template', 't', InputOption::VALUE_REQUIRED, 'Template file for the badges.')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output directory for the badges.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url      = $input->getOption('url');
        $username = parse_url($url, PHP_URL_USER);
        $password = parse_url($url, PHP_URL_PASS);

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
            $output->writeln(sprintf('Connecting to %s …', $url));
        }

        $env      = new \Twig_Environment(new \Twig_Loader_String());
        $template = file_get_contents($input->getOption('template'));

        // Fetch list of tickets which need to be printed
        $queue = json_decode(file_get_contents($url));
        foreach ($queue->items as $ticket) {
            $ticketUrl = $ticket->{'@subject'};
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
                $output->writeln(sprintf('Printing %s …', $ticketUrl));
            }
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln(sprintf('%s (%s)', $ticket->code, $ticket->name));
            }
            // Create bade
            $data            = $ticket;
            $nameParts       = explode(' ', $ticket->name, 2);
            $tags            = explode(' ', $ticket->tags);
            $data->tag1      = isset($tags[0]) ? $tags[0] : null;
            $data->tag2      = isset($tags[1]) ? $tags[1] : null;
            $data->tag3      = isset($tags[2]) ? $tags[2] : null;
            $data->firstname = $nameParts[0];
            $data->lastname  = isset($nameParts[1]) ? $nameParts[1] : null;
            $data->day       = $ticket->day == Ticket::DAY_SATURDAY ? 'Sa' : 'So';
            $badge           = $env->render($template, (array)$data);
            $badgeFileName   = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $ticket->code;
            $badgeSVG        = $badgeFileName . '.svg';
            $badgePDF        = $badgeFileName . '.pdf';
            file_put_contents($badgeSVG, $badge);
            exec(
                sprintf(
                    '`which inkscape` --export-pdf=%s %s',
                    escapeshellarg($badgePDF),
                    escapeshellarg($badgeSVG)
                )
            );
            $printFile = rtrim($input->getOption('output'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $ticket->code . '.pdf';
            if (is_file($printFile)) {
                $counter = 1;
                do {
                    $printFile = preg_replace('/(\.[0-9]+)*\.pdf$/', '.' . ($counter++) . '.pdf', $printFile);
                } while (is_file($printFile));
            }
            copy($badgePDF, $printFile);
            unlink($badgePDF);
            unlink($badgeSVG);
            // Mark ticket as printed
            $context = array(
                'http' => array(
                    'method' => "PATCH",
                    'header' => sprintf(
                        'Authorization: Basic %s',
                        base64_encode(
                            sprintf('%s:%s', $username, $password)
                        )),
                )
            );
            file_get_contents($ticketUrl, null, stream_context_create($context));
        }
    }
}

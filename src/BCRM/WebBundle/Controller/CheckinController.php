<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Controller;

use BCRM\BackendBundle\Entity\Event\Ticket;
use BCRM\BackendBundle\Entity\Event\TicketRepository;
use BCRM\BackendBundle\Service\Concierge\CheckinCommand;
use BCRM\WebBundle\Content\ContentReader;
use LiteCQRS\Bus\CommandBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Manages event registrations.
 */
class CheckinController
{
    /**
     * @var ContentReader
     */
    private $reader;

    /**
     * @var \BCRM\BackendBundle\Entity\Event\TicketRepository
     */
    private $ticketRepo;

    public function __construct(ContentReader $reader, TicketRepository $ticketRepo, CommandBus $commandBus)
    {
        $this->reader     = $reader;
        $this->ticketRepo = $ticketRepo;
        $this->commandBus = $commandBus;
    }

    /**
     * @param Request $request
     * @param         $id
     * @param         $code
     *
     * @return array|RedirectResponse
     * @Template()
     */
    public function checkinAction(Request $request, $id, $code)
    {
        /* @var $ticket Ticket */
        $ticket = $this->ticketRepo->getTicketByIdAndCode($id, $code)->getOrThrow(new NotFoundHttpException('Unknown ticket.'));

        $command = new CheckinCommand();
        $command->ticket = $ticket;
        $this->commandBus->handle($command);

        return array(
            'ticket'   => $ticket,
            'sponsors' => $this->reader->getPage('Sponsoren/Index.md'),
        );
    }
}

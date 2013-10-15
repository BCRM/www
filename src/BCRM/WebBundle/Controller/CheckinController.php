<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Controller;

use BCRM\BackendBundle\Entity\Event\Ticket;
use BCRM\BackendBundle\Entity\Event\TicketRepository;
use BCRM\WebBundle\Content\ContentReader;
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

    public function __construct(ContentReader $reader, TicketRepository $ticketRepo)
    {
        $this->reader     = $reader;
        $this->ticketRepo = $ticketRepo;
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

        if ($request->isMethod('POST')) {
            // TODO: implement
        }

        return array(
            'ticket'   => $ticket,
            'sponsors' => $this->reader->getPage('Sponsoren/Index.md'),
        );
    }
}

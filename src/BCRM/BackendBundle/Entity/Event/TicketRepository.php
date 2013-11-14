<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Entity\Event;

use PhpOption\Option;

interface TicketRepository
{
    /**
     * @param Event $event
     *
     * @return Ticket[]
     */
    public function getNewTickets(Event $event);

    /**
     * @param Event $event
     *
     * @return Ticket[]
     */
    public function getTicketsForEvent(Event $event);

    /**
     * @param Event  $event
     * @param string $email
     *
     * @return Ticket[]
     */
    public function getTicketsForEmail(Event $event, $email);

    /**
     * @param integer $id
     * @param string  $code
     *
     * @return Option
     */
    public function getTicketByIdAndCode($id, $code);

    /**
     * Returns the number of tickets for the given day.
     *
     * @param Event $event
     * @param       $day
     *
     * @return integer
     */
    public function getTicketCountForEvent(Event $event, $day);

    /**
     * Returns the number of checkins for the given day.
     *
     * @param Event $event
     * @param       $day
     *
     * @return integer
     */
    public function getCheckinCountForEvent(Event $event, $day);

    /**
     * Returns the list of unprinted tickets for the given day.
     *
     * @param Event $event
     * @param       $day
     *
     * @return Ticket[]
     */
    public function getUnprintedTickets(Event $event, $day);

    /**
     * Search tickets matching the given term
     *
     * @param Event $event
     * @param       $day
     * @param       $term
     *
     * @return mixed
     */
    public function searchTickets(Event $event, $day, $term);
}


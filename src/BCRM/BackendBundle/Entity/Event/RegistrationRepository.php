<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Entity\Event;

use Doctrine\Common\Collections\ArrayCollection;
use PhpOption\Option;

interface RegistrationRepository
{
    /**
     * @return Registration[]
     */
    public function getNewRegistrations();

    /**
     * @param string $id
     * @param string $key
     *
     * @return \PhpOption\Option
     */
    public function getRegistrationByIdAndKey($id, $key);

    /**
     * Returns up to $capacity registrations for the event $event without tickets for the given day $day.
     * 
     * @param Event   $event
     * @param integer $day
     * @param integer $capacity
     *
     * @return Registration[]
     */
    public function getNextRegistrations(Event $event, $day, $capacity);

    /**
     * Returns VIP registrations for the event $event without tickets for the given day $day.
     * 
     * @param Event   $event
     * @param integer $day
     *
     * @return Registration[]
     */
    public function getNextVipRegistrations(Event $event, $day);

    /**
     * @param Event $event
     * @param string $email
     *
     * @return Option
     */
    public function getRegistrationForEmail(Event $event, $email);

    /**
     * Returns the public participant list for the given event.
     *
     * @param Event $event
     *
     * @return Registration[]|ArrayCollection
     */
    public function getParticipantList(Event $event);
}

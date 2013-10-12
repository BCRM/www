<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Entity\Event;

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
     * @param Registration $registration
     *
     * @return void
     */
    public function confirmRegistration(Registration $registration);

    /**
     * @param Registration $registration
     * @param string       $key
     *
     * @return void
     */
    public function initConfirmation(Registration $registration, $key);
}

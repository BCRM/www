<?php
/**
 * @author    Markus Tacker <m@coderbyheart.com>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Tests\Unit\Entity\Event;

use BCRM\BackendBundle\Entity\Event\Ticket;
use BCRM\BackendBundle\Entity\Event\Registration;

class TicketTest extends \PHPUnit_Framework_TestCase
{
    public function labels()
    {
        return array(
            array(Registration::TYPE_NORMAL, ''),
            array(Registration::TYPE_SPONSOR, 'Sponsor'),
            array(Registration::TYPE_VIP, 'VIP')
        );
    }
    
    /**
     * @test
     * @group        unit
     * @dataProvider labels
     */
    public function ticketShouldHaveLabel($type, $label)
    {
        $ticket = new Ticket();
        $ticket->setType($type);
        $this->assertEquals($label, $ticket->getLabel());
    }
}

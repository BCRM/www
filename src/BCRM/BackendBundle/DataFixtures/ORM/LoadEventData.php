<?php

namespace BCRM\BackendBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use BCRM\BackendBundle\Entity\Event\Event;

class LoadEventData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $bcrm13 = new Event();
        $bcrm13->setCapacity(3);
        $bcrm13->setName('BarCamp RheinMain 2013 Dieburg 23./24.11.2013');
        $bcrm13->setRegistrationStart(new \DateTime('2013-10-14T08:00:00+02:00'));
        $bcrm13->setRegistrationEnd(new \DateTime('2013-11-23T14:00:00+02:00'));
        // During our tests we will simulate the sunday
        $start = new \DateTime();
        $start->setTime(8, 0, 0);
        $start->modify('-1day');
        $bcrm13->setStart($start);

        $manager->persist($bcrm13);
        $manager->flush();
    }
}

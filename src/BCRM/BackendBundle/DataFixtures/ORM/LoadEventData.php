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
        $bcrm13->setCapacity(350);
        $bcrm13->setName('BarCamp RheinMain 2013 Dieburg 23./24.11.2013');
        $bcrm13->setRegistrationStart(new \DateTime('2013-10-14T08:00:00+02:00'));
        $bcrm13->setStart(new \DateTime('2013-11-23T08:30:00+02:00'));

        $manager->persist($bcrm13);
        $manager->flush();
    }
}

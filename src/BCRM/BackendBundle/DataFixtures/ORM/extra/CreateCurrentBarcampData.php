<?php

namespace BCRM\BackendBundle\DataFixtures\ORM;

use Carbon\Carbon;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use BCRM\BackendBundle\Entity\Event\Event;

class CreateCurrentBarcampData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $nextBcrm = new Event();
        $nextBcrm->setCapacity(400);
        $startDate         = Carbon::create()->addDays(30);
        $endDate           = Carbon::create()->addDays(31);
        $registrationStart = Carbon::create();
        $nextBcrm->setName(sprintf(
            'BarCamp %s %s/%s',
            $startDate->format('Y'),
            $startDate->format('d.'),
            $endDate->format('d.m.Y')
        ));
        $nextBcrm->setStart($startDate);
        $nextBcrm->setRegistrationStart($registrationStart);
        $nextBcrm->setRegistrationEnd($endDate);
        $nextBcrm->setPrice(1000);
        $manager->persist($nextBcrm);
        $manager->flush();
    }
}

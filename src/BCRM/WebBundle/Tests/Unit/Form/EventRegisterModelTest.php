<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Tests\Unit\Form;

class EventRegisterModelTest extends \PHPUnit_Framework_TestCase
{
    public function provider()
    {
        return array(
            array(0, false, false),
            array(1, true, false),
            array(2, false, true),
            array(3, true, true)
        );
    }

    /**
     * @test
     * @group        unit
     * @dataProvider provider
     */
    public function itShouldReportTheCorrectDays($daysValue, $hasSaturday, $hasSunday)
    {
        $model       = $this->createTestObject();
        $model->days = $daysValue;
        $this->assertEquals($model->wantsSaturday(), $hasSaturday);
        $this->assertEquals($model->wantsSunday(), $hasSunday);
    }

    /**
     * @test
     * @group        unit
     */
    public function itShouldConvertEur()
    {
        $model = $this->createTestObject();
        $model->setDonation(100);
        $this->assertEquals($model->getDonation(), 100);
        $this->assertEquals($model->getDonationEur(), 1.0);

        $model->setDonationEur(123.45);
        $this->assertEquals($model->getDonation(), 12345);
        $this->assertEquals($model->getDonationEur(), 123.45);

        $model->setDonationEur('17,17');
        $this->assertEquals($model->getDonation(), 1717);
        $this->assertEquals($model->getDonationEur(), 17.17);
    }

    /**
     * @return \BCRM\WebBundle\Form\EventRegisterModel
     */
    protected function createTestObject()
    {
        $model = new \BCRM\WebBundle\Form\EventRegisterModel();
        return $model;
    }

}

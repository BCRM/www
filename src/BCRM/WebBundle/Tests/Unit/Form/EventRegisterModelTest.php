<?php
/**
 * @author Markus Tacker <m@coderbyheart.de>
 */

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
        $model       = new \BCRM\WebBundle\Form\EventRegisterModel();
        $model->days = $daysValue;
        $this->assertEquals($model->wantsSaturday(), $hasSaturday);
        $this->assertEquals($model->wantsSunday(), $hasSunday);
    }

}

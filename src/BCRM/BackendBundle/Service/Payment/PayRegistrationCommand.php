<?php

namespace BCRM\BackendBundle\Service\Payment;


use BCRM\BackendBundle\Entity\Event\Registration;
use BCRM\BackendBundle\Entity\Payment;

class PayRegistrationCommand
{
    /**
     * @var Registration
     */
    public $registration;

    /**
     * @var Payment
     */
    public $payment;
}

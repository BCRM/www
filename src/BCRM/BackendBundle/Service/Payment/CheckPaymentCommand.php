<?php

namespace BCRM\BackendBundle\Service\Payment;

use BCRM\BackendBundle\Entity\Payment;

class CheckPaymentCommand
{
    /**
     * @var  Payment
     */
    public $payment;

    /**
     * @var bool
     */
    public $sandbox;
}

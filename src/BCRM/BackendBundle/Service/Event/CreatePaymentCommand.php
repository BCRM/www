<?php

namespace BCRM\BackendBundle\Service\Event;


class CreatePaymentCommand
{
    public $txId;
    public $payload;
    public $method;
}

<?php

namespace BCRM\BackendBundle\Event\Payment;

use LiteCQRS\Bus\EventMessageHeader;
use LiteCQRS\DomainEvent;

class PaymentVerifiedEvent implements DomainEvent
{
    /**
     * @var \BCRM\BackendBundle\Entity\Payment
     */
    public $payment;

    private $messageHeader;

    public function __construct()
    {
        $this->messageHeader = new EventMessageHeader();
    }

    public function getEventName()
    {
        return 'PaymentVerified';
    }

    public function getMessageHeader()
    {
        return $this->messageHeader;
    }

    public function getAggregateId()
    {
        return $this->messageHeader->aggregateId;
    }
}

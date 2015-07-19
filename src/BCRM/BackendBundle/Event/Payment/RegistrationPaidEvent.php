<?php

namespace BCRM\BackendBundle\Event\Payment;


use BCRM\BackendBundle\Entity\Event\Registration;
use BCRM\BackendBundle\Entity\Payment;
use LiteCQRS\Bus\EventMessageHeader;
use LiteCQRS\DomainEvent;

class RegistrationPaidEvent implements DomainEvent
{
    /**
     * @var Registration
     */
    public $registration;

    /**
     * @var Payment
     */
    public $payment;

    private $messageHeader;

    public function __construct()
    {
        $this->messageHeader = new EventMessageHeader();
    }

    public function getEventName()
    {
        return 'RegistrationPaid';
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

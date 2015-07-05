<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service;

use BCRM\BackendBundle\Event\Payment\PaymentVerifiedEvent;
use BCRM\BackendBundle\Service\Event\CreatePaymentCommand;
use BCRM\BackendBundle\Service\Payment\CheckPaymentCommand;
use LiteCQRS\Bus\CommandBus;
use LiteCQRS\Bus\EventMessageBus;
use LiteCQRS\Plugin\CRUD\Model\Commands\CreateResourceCommand;
use LiteCQRS\Plugin\CRUD\Model\Commands\UpdateResourceCommand;

class Payment
{
    /**
     * @var \LiteCQRS\Bus\CommandBus
     */
    private $commandBus;

    /**
     * @param CommandBus      $commandBus
     * @param EventMessageBus $eventMessageBus
     */
    public function __construct(CommandBus $commandBus, EventMessageBus $eventMessageBus)
    {
        $this->commandBus      = $commandBus;
        $this->eventMessageBus = $eventMessageBus;
    }

    public function createPayment(CreatePaymentCommand $command)
    {
        $createCommand        = new CreateResourceCommand();
        $createCommand->class = '\BCRM\BackendBundle\Entity\Payment';
        $createCommand->data  = array(
            'txId'    => $command->txId,
            'payload' => $command->payload,
            'method'  => $command->method
        );
        $this->commandBus->handle($createCommand);
    }

    public function checkPayment(CheckPaymentCommand $command)
    {
        // Verify transaction
        $data        = $command->payment->getPayload()->toArray();
        $data['cmd'] = '_notify-validate';

        $context = array(
            'http' => array(
                'method'  => "POST",
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($data),
            )
        );
        $url     = $command->sandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
        $res     = file_get_contents($url, null, stream_context_create($context));

        $updateCommand        = new UpdateResourceCommand();
        $updateCommand->class = '\BCRM\BackendBundle\Entity\Payment';
        $updateCommand->id    = $command->payment->getId();
        $updateCommand->data  = array('checked' => new \DateTime());

        if (strcmp($res, "VERIFIED") == 0) {
            $updateCommand->data['verified'] = true;
            $event                           = new PaymentVerifiedEvent();
            $event->payment                  = $command->payment;
            $this->eventMessageBus->publish($event);
        }

        $this->commandBus->handle($updateCommand);
    }
}

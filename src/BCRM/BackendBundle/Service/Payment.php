<?php

/**
 * @author    Markus Tacker <m@coderbyheart.com>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service;

use BCRM\BackendBundle\Event\Payment\PaymentVerifiedEvent;
use BCRM\BackendBundle\Service\Event\CreatePaymentCommand;
use BCRM\BackendBundle\Service\Payment\CheckPaymentCommand;
use LiteCQRS\Bus\CommandBus;
use LiteCQRS\Bus\EventMessageBus;
use LiteCQRS\Plugin\CRUD\Model\Commands\CreateResourceCommand;
use LiteCQRS\Plugin\CRUD\Model\Commands\UpdateResourceCommand;
use PhpOption\Option;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class Payment implements LoggerAwareInterface
{
    /**
     * @var \LiteCQRS\Bus\CommandBus
     */
    private $commandBus;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $paypalIdentityToken;

    /**
     * @param CommandBus      $commandBus
     * @param EventMessageBus $eventMessageBus
     */
    public function __construct(CommandBus $commandBus, EventMessageBus $eventMessageBus, $paypalIdentityToken)
    {
        $this->commandBus          = $commandBus;
        $this->eventMessageBus     = $eventMessageBus;
        $this->paypalIdentityToken = $paypalIdentityToken;
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     *
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
        $payment = $command->payment;

        $url = $command->sandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
        $url .= sprintf('?tx=%s&at=%s&cmd=_notify-synch', $payment->getTransactionId(), $this->paypalIdentityToken);

        $res = file_get_contents($url);

        $data = array();
        foreach (explode("\n", $res) as $line) {
            if (!strpos($line, '=')) continue;
            list($key, $value) = explode('=', $line);
            $data[$key] = $value;
        }

        $updateCommand        = new UpdateResourceCommand();
        $updateCommand->class = '\BCRM\BackendBundle\Entity\Payment';
        $updateCommand->id    = $payment->getId();
        $updateCommand->data  = array('checked' => new \DateTime());

        if (substr($res, 0, 7) === 'SUCCESS' && $data['payment_status'] === 'Completed') {
            $updateCommand->data['verified'] = true;
            $event                           = new PaymentVerifiedEvent();
            $event->payment                  = $payment;
            $payment->setPayload(new ArrayCollection($data));
            $this->eventMessageBus->publish($event);
            Option::fromValue($this->logger)->map(function (LoggerInterface $logger) use ($payment) {
                $logger->info(sprintf('Payment "%s" is verified.', $payment->getId()), array($payment));
            });
        } else {
            Option::fromValue($this->logger)->map(function (LoggerInterface $logger) use ($payment) {
                $logger->alert(sprintf('Payment "%s" could not be verified.', $payment->getId()), array($payment));
            });
        }

        $this->commandBus->handle($updateCommand);
    }
}

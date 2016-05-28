<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service;

use BCRM\BackendBundle\Event\Concierge\CheckedInEvent;
use BCRM\BackendBundle\Service\Concierge\CheckinCommand;
use BCRM\BackendBundle\Service\Concierge\PayRegistrationConciergeCommand;
use BCRM\BackendBundle\Entity\PaymentRepository;
use BCRM\BackendBundle\Entity\Event\RegistrationRepository;
use Endroid\QrCode\QrCode;
use LiteCQRS\Bus\CommandBus;
use LiteCQRS\Bus\EventMessageBus;
use LiteCQRS\Plugin\CRUD\Model\Commands\UpdateResourceCommand;
use LiteCQRS\Plugin\CRUD\Model\Commands\CreateResourceCommand;
use Symfony\Component\Routing\RouterInterface;
use LiteCQRS\Plugin\CRUD\Model\Events\ResourceCreatedEvent;

class Concierge
{
    /**
     * @var \LiteCQRS\Bus\CommandBus
     */
    private $commandBus;

    /**
     * @var \LiteCQRS\Bus\EventMessageBus
     */
    private $eventMessageBus;

    /**
     * @param CommandBus             $commandBus
     * @param RouterInterface        $router
     * @param PaymentRepository      $paymentRepo
     * @param RegistrationRepository $registrationRepo
     */
    public function __construct(
        CommandBus $commandBus,
        EventMessageBus $eventMessageBus,
        PaymentRepository $paymentRepo,
        RegistrationRepository $registrationRepo
    )
    {
        $this->commandBus       = $commandBus;
        $this->eventMessageBus  = $eventMessageBus;
        $this->paymentRepo      = $paymentRepo;
        $this->registrationRepo = $registrationRepo;
    }

    public function checkin(CheckinCommand $command)
    {
        $updateCommand        = new UpdateResourceCommand();
        $updateCommand->class = '\BCRM\BackendBundle\Entity\Event\Ticket';
        $updateCommand->id    = $command->ticket->getId();
        $updateCommand->data  = array('checkedIn' => new \DateTime());
        $this->commandBus->handle($updateCommand);

        $event         = new CheckedInEvent();
        $event->ticket = $command->ticket;
        $this->eventMessageBus->publish($event);
    }

    public function payRegistrationConcierge(PayRegistrationConciergeCommand $command)
    {
        $createCommand        = new CreateResourceCommand();
        $createCommand->class = '\BCRM\BackendBundle\Entity\Payment';
        $createCommand->data  = array(
            'txId'     => $command->uuid,
            'payload'  => [],
            'method'   => 'concierge',
            'checked'  => new \DateTime(),
            'verified' => '1'
        );
        $this->commandBus->handle($createCommand);

        $paymentOption = $this->paymentRepo->findByTxId($command->uuid);
        if ($paymentOption->isDefined()) {

        }
    }

    public function onResourceCreated(ResourceCreatedEvent $event)
    {
        if ($event->class !== 'BCRM\BackendBundle\Entity\Payment') {
            return;
        }
        $registrationOption = $this->registrationRepo->findByUuid($event->data['txId']);
        if ($registrationOption->isDefined()) {
            $payRegistrationCommand               = new \BCRM\BackendBundle\Service\Payment\PayRegistrationCommand();
            $payRegistrationCommand->registration = $registrationOption->get();
            $payRegistrationCommand->payment      = $this->paymentRepo->findByTxId($event->data['txId'])->get();
            $this->commandBus->handle($payRegistrationCommand);
        }
    }
}

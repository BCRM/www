<?php

/**
 * @author    Markus Tacker <m@coderbyheart.com>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Controller;

use BCRM\BackendBundle\Service\Event\CreatePaymentCommand;
use LiteCQRS\Bus\CommandBus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Manages Paypal webhooks notifications
 */
class PaypalController
{
    /**
     * @var \LiteCQRS\Bus\CommandBus
     */
    private $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function webhookAction(Request $request)
    {
        $command          = new CreatePaymentCommand();
        $command->payload = $request->request->all();
        $command->txId    = $command->payload['txn_id'];
        $command->method  = 'paypal';
        $this->commandBus->handle($command);
        return new Response();
    }
}

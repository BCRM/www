<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service;

use LiteCQRS\Bus\CommandBus;
use LiteCQRS\Plugin\CRUD\Model\Commands\CreateResourceCommand;
use Symfony\Component\Routing\RouterInterface;

class Newsletter
{
    /**
     * @var \LiteCQRS\Bus\CommandBus
     */
    private $commandBus;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @param CommandBus      $commandBus
     * @param RouterInterface $router
     */
    public function __construct(CommandBus $commandBus, RouterInterface $router)
    {
        $this->commandBus = $commandBus;
        $this->router     = $router;
    }

    public function newsletterSubscribe(NewsletterSubscribeCommand $command)
    {
        $createSubscriptionCommand        = new CreateResourceCommand();
        $createSubscriptionCommand->class = '\BCRM\BackendBundle\Entity\Newsletter\Subscription';
        $createSubscriptionCommand->data  = array('email' => $command->email, 'futureBarcamps' => $command->futurebarcamps);
        $this->commandBus->handle($createSubscriptionCommand);
    }
}
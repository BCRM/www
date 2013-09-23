<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service;

use BCRM\BackendBundle\Service\Mail\SendTemplateMailCommand;
use BCRM\BackendBundle\Service\Newsletter\SendConfirmationMailCommand;
use BCRM\BackendBundle\Service\Newsletter\SubscribeCommand;
use LiteCQRS\Bus\CommandBus;
use LiteCQRS\Plugin\CRUD\Model\Commands\CreateResourceCommand;
use LiteCQRS\Plugin\CRUD\Model\Commands\UpdateResourceCommand;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Util\SecureRandom;

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

    public function subscribe(SubscribeCommand $command)
    {
        $createSubscriptionCommand        = new CreateResourceCommand();
        $createSubscriptionCommand->class = '\BCRM\BackendBundle\Entity\Newsletter\Subscription';
        $createSubscriptionCommand->data  = array('email' => $command->email, 'futureBarcamps' => $command->futurebarcamps);
        $this->commandBus->handle($createSubscriptionCommand);
    }

    public function sendConfirmationMail(SendConfirmationMailCommand $command)
    {
        $updateCommand        = new UpdateResourceCommand();
        $updateCommand->class = '\BCRM\BackendBundle\Entity\Newsletter\Subscription';
        $updateCommand->id    = $command->subscription->getId();
        $sr                   = new SecureRandom();
        $key                  = sha1($sr->nextBytes(256), false);
        $updateCommand->data  = array('confirmationKey' => $key);
        $this->commandBus->handle($updateCommand);

        $emailCommand               = new SendTemplateMailCommand();
        $emailCommand->email        = $command->subscription->getEmail();
        $emailCommand->template     = 'NewsletterConfirmation';
        $emailCommand->templateData = array(
            'subscription'      => $command->subscription,
            'confirmation_link' => rtrim($command->schemeAndHost, '/') . $this->router->generate('bcrm_newsletter_confirm', array('id' => $command->subscription->getId(), 'key' => $key))
        );
        $this->commandBus->handle($emailCommand);
    }
}
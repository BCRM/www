<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Controller;

use BCRM\BackendBundle\Entity\Newsletter\SubscriptionRepository;
use BCRM\BackendBundle\Service\Newsletter\SubscribeCommand;
use BCRM\WebBundle\Content\ContentReader;
use BCRM\WebBundle\Form\NewsletterSubscribeModel;
use BCRM\WebBundle\Form\NewsletterSubscribeType;
use LiteCQRS\Bus\CommandBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * Manages newsletter subscriptions.
 */
class NewsletterController
{
    /**
     * @var ContentReader
     */
    private $reader;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \LiteCQRS\Bus\CommandBus
     */
    private $commandBus;

    /**
     * @var \BCRM\BackendBundle\Entity\Newsletter\SubscriptionRepository
     */
    private $repo;

    public function __construct(ContentReader $reader, FormFactoryInterface $formFactory, RouterInterface $router, CommandBus $commandBus, SubscriptionRepository $repo)
    {
        $this->reader      = $reader;
        $this->formFactory = $formFactory;
        $this->router      = $router;
        $this->commandBus  = $commandBus;
        $this->repo        = $repo;
    }

    /**
     * @param Request $request
     *
     * @Template()
     */
    public function subscribeAction(Request $request)
    {
        $form = $this->formFactory->create(new NewsletterSubscribeType());
        $form->handleRequest($request);
        if ($form->isValid()) {
            /* @var NewsletterSubscribeModel $formData */
            $formData     = $form->getData();
            $subscription = $this->repo->getSubscription($formData->email);
            if ($subscription->isDefined()) {
                return new RedirectResponse($this->router->generate('bcrmweb_newsletter_already_subscribed'));
            }
            $command                 = new NewsletterSubscribeCommand();
            $command->email          = $formData->email;
            $command->futurebarcamps = $formData->futurebarcamps;
            $this->commandBus->handle($command);
            return new RedirectResponse($this->router->generate('bcrmweb_newsletter_ok'));
        }
        return array(
            'sponsors' => $this->reader->getPage('Sponsoren/Index'),
            'form'     => $form->createView(),
        );
    }
}

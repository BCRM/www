<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Controller;

use BCRM\BackendBundle\Entity\Event\EventRepository;
use BCRM\BackendBundle\Entity\Event\RegistrationRepository;
use BCRM\BackendBundle\Service\Event\ConfirmRegistrationCommand;
use BCRM\BackendBundle\Service\Event\RegisterCommand;
use BCRM\WebBundle\Content\ContentReader;
use BCRM\WebBundle\Form\EventRegisterModel;
use BCRM\WebBundle\Form\EventRegisterType;
use LiteCQRS\Bus\CommandBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Manages event registrations.
 */
class EventController
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
     * @var \BCRM\BackendBundle\Entity\Event\RegistrationRepository
     */
    private $registrationRepo;

    /**
     * @var \BCRM\BackendBundle\Entity\Event\EventRepository
     */
    private $eventRepo;

    public function __construct(ContentReader $reader, FormFactoryInterface $formFactory, RouterInterface $router, CommandBus $commandBus, EventRepository $eventRepo, RegistrationRepository $registrationRepo)
    {
        $this->reader           = $reader;
        $this->formFactory      = $formFactory;
        $this->router           = $router;
        $this->commandBus       = $commandBus;
        $this->eventRepo        = $eventRepo;
        $this->registrationRepo = $registrationRepo;
    }

    /**
     * @param Request $request
     *
     * @Template()
     */
    public function registerAction(Request $request)
    {
        $form = $this->formFactory->create(new EventRegisterType());
        $form->handleRequest($request);
        if ($form->isValid()) {
            /* @var EventRegisterModel $formData */
            $formData          = $form->getData();
            $command           = new RegisterCommand();
            $command->event    = $this->eventRepo->getNextEvent()->getOrThrow(new AccessDeniedHttpException('No event.'));
            $command->email    = $formData->email;
            $command->name     = $formData->name;
            $command->saturday = $formData->wantsSaturday();
            $command->sunday   = $formData->wantsSunday();
            $command->arrival  = $formData->arrival;
            $this->commandBus->handle($command);
            return new RedirectResponse($this->router->generate('bcrmweb_registration_ok'));
        }
        return array(
            'sponsors' => $this->reader->getPage('Sponsoren/Index.md'),
            'form'     => $form->createView(),
        );
    }

    public function confirmRegistrationAction($id, $key)
    {
        $registration = $this->registrationRepo->getRegistrationByIdAndKey($id, $key);
        if ($registration->isEmpty()) {
            throw new NotFoundHttpException('Unknown registration.');
        }
        $command               = new ConfirmRegistrationCommand();
        $command->registration = $registration->get();
        $this->commandBus->handle($command);
        return new RedirectResponse($this->router->generate('bcrmweb_registration_confirmed'));
    }
}

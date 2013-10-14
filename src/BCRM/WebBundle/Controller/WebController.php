<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Controller;

use BCRM\BackendBundle\Entity\Event\EventRepository;
use BCRM\BackendBundle\Exception\FileNotFoundException;
use BCRM\WebBundle\Content\ContentReader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Renders the page.
 */
class WebController
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
     * @var \BCRM\BackendBundle\Entity\Event\EventRepository
     */
    private $eventRepo;

    public function __construct(ContentReader $reader, FormFactoryInterface $formFactory, RouterInterface $router, EventRepository $eventRepo)
    {
        $this->reader      = $reader;
        $this->formFactory = $formFactory;
        $this->router      = $router;
        $this->eventRepo   = $eventRepo;
    }

    /**
     * Render the index page.
     *
     * @param Request $request
     *
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $response = $this->pageAction($request, 'Index');
        if ($response instanceof Response) return $response;
        $nextEvent = $this->eventRepo->getNextEvent();
        if ($nextEvent->isDefined()) {
            $response['nextEvent'] = $nextEvent;
        }
        return $response;
    }

    /**
     * Render a content page.
     *
     * @param Request $request
     * @param string  $path
     *
     * @Template()
     * @return Response|array
     * @throws NotFoundHttpException
     */
    public function pageAction(Request $request, $path)
    {
        try {
            $pageInfo = $this->reader->getInfo($path . '.md');
        } catch (FileNotFoundException $e) {
            throw new NotFoundHttpException();
        }
        $response = new Response();
        $response->setETag($pageInfo->getEtag());
        $response->setLastModified($pageInfo->getLastModified());
        $response->setPublic();
        if ($response->isNotModified($request)) {
            return $response;
        }

        $p = $this->reader->getPage($path . '.md');
        if ($p->isHidden()) throw new NotFoundHttpException();
        return array(
            'page'     => $p,
            'path'     => $path,
            'sponsors' => $this->reader->getPage('Sponsoren/Index.md')
        );
    }

    /**
     * Render a content page without the body. Used for ajax calls.
     *
     * @param Request $request
     * @param         $path
     *
     * @return Response
     */
    public function contentAction(Request $request, $path)
    {
        $pageInfo = $this->reader->getInfo($path . '.md');
        $response = new Response();
        $response->setETag($pageInfo->getEtag());
        $response->setLastModified($pageInfo->getLastModified());
        $response->setPublic();
        if ($response->isNotModified($request)) {
            return $response;
        }

        $p = $this->reader->getPage($path . '.md');
        $response->setContent($p->getContent());
        return $response;
    }
}

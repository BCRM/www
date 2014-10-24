<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Controller;

use BCRM\BackendBundle\Entity\Event\Event;
use BCRM\BackendBundle\Entity\Event\EventRepository;
use BCRM\BackendBundle\Exception\FileNotFoundException;
use BCRM\WebBundle\Content\ContentReader;
use Carbon\Carbon;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
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

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     */
    private $renderer;

    /**
     * @var int Unix timestamp of assets modification
     */
    private $assetsVersion;

    public function __construct(
        ContentReader $reader,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        EventRepository $eventRepo,
        EngineInterface $renderer,
        $assetsVersion
    )
    {
        $this->reader        = $reader;
        $this->formFactory   = $formFactory;
        $this->router        = $router;
        $this->eventRepo     = $eventRepo;
        $this->renderer      = $renderer;
        $this->assetsVersion = $assetsVersion;
    }

    /**
     * Render the index page.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $response = $this->pageAction($request, 'Index');
        if ($response->isNotModified($request)) {
            return $response;
        }
        $nextEvent = $this->eventRepo->getNextEvent();

        $data = array(
            'page'     => $this->reader->getPage('Index.md'),
            'path'     => 'Index',
            'sponsors' => $this->reader->getPage('Sponsoren/Index.md')
        );
        if ($nextEvent->isDefined()) {
            /* @var Event $event */
            $event = $nextEvent->get();
            if (Carbon::createFromTimestamp($event->getRegistrationEnd()->getTimestamp())->isFuture()) {
                $data ['nextEvent'] = $nextEvent;
            }
        }
        return $this->renderer->renderResponse('BCRMWebBundle:Web:index.html.twig', $data, $response);
    }

    /**
     * Render a content page.
     *
     * @param Request $request
     * @param string  $path
     *
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
        $lastModified = $pageInfo->getLastModified();
        if ($this->assetsVersion - $pageInfo->getLastModified()->getTimestamp() > 0) {
            $lastModified = new \DateTime('@' . $this->assetsVersion);
        }
        $response->setLastModified($lastModified);
        $response->setPublic();
        if ($response->isNotModified($request)) {
            return $response;
        }

        $p = $this->reader->getPage($path . '.md');
        if ($p->isHidden()) throw new NotFoundHttpException();

        return $this->renderer->renderResponse('BCRMWebBundle:Web:page.html.twig', array(
            'page'     => $p,
            'path'     => $path,
            'sponsors' => $this->reader->getPage('Sponsoren/Index.md')
        ), $response);
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
        $lastModified = $pageInfo->getLastModified();
        if ($this->assetsVersion - $pageInfo->getLastModified()->getTimestamp() > 0) {
            $lastModified = new \DateTime('@' . $this->assetsVersion);
        }
        $response->setLastModified($lastModified);
        $response->setPublic();
        if ($response->isNotModified($request)) {
            return $response;
        }

        $p = $this->reader->getPage($path . '.md');
        $response->setContent($p->getContent());
        return $response;
    }
}

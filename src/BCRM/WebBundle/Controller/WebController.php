<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Controller;

use BCRM\WebBundle\Content\ContentReader;
use BCRM\WebBundle\Form\NewsletterSubscribeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    public function __construct(ContentReader $reader, FormFactoryInterface $formFactory)
    {
        $this->reader      = $reader;
        $this->formFactory = $formFactory;
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
        $form             = $this->formFactory->create(new NewsletterSubscribeType());
        $response['form'] = $form->createView();
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
     */
    public function pageAction(Request $request, $path)
    {
        $pageInfo = $this->reader->getInfo($path);
        $response = new Response();
        $response->setETag($pageInfo->getEtag());
        $response->setLastModified($pageInfo->getLastModified());
        $response->setPublic();
        if ($response->isNotModified($request)) {
            return $response;
        }

        $p = $this->reader->getPage($path);
        return array(
            'page'     => $p,
            'path'     => $path,
            'sponsors' => $this->reader->getPage('Sponsoren/Index')
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
        $pageInfo = $this->reader->getInfo($path);
        $response = new Response();
        $response->setETag($pageInfo->getEtag());
        $response->setLastModified($pageInfo->getLastModified());
        $response->setPublic();
        if ($response->isNotModified($request)) {
            return $response;
        }

        $p = $this->reader->getPage($path);
        $response->setContent($p->getContent());
        return $response;
    }
}

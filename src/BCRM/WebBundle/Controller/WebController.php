<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Controller;

use BCRM\WebBundle\Content\ContentReader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    public function __construct(ContentReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @Template()
     */
    public function indexAction()
    {
        return $this->pageAction('Index');
    }

    /**
     * @Template()
     */
    public function pageAction($path)
    {
        $p = $this->reader->getPage($path);
        return array(
            'page'     => $p,
            'path'     => $path,
            'sponsors' => $this->reader->getPage('Sponsoren/Index')
        );
    }

    public function contentAction($path)
    {
        $p = $this->reader->getPage($path);
        return new Response($p->getContent());
    }
}

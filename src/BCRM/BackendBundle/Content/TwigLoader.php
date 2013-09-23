<?php

namespace BCRM\BackendBundle\Content;

use Twig_Error_Loader;

class TwigLoader implements \Twig_LoaderInterface
{
    /**
     * @var ContentReader
     */
    private $cr;

    public function __construct(ContentReader $cr)
    {
        $this->cr = $cr;
    }

    /**
     * Gets the source code of a template, given its name.
     *
     * @param string $name The name of the template to load
     *
     * @return string The template source code
     *
     * @throws Twig_Error_Loader When $name is not found
     */
    public function getSource($name)
    {
        $template = $this->cr->getContent($this->getFile($name));
        return $template->getContent();
    }

    protected function getFile($name)
    {
        if (!preg_match('/^bcrm_content:(.+)/', $name, $match)) throw new Twig_Error_Loader(sprintf('Unknown template: %s', $name));
        return $match[1];
    }

    /**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @param string $name The name of the template to load
     *
     * @return string The cache key
     *
     * @throws Twig_Error_Loader When $name is not found
     */
    public function getCacheKey($name)
    {
        return md5($this->getFile($name));
    }

    /**
     * Returns true if the template is still fresh.
     *
     * @param string $name The template name
     * @param int    $time The last modification time of the cached template
     *
     * @return Boolean true if the template is fresh, false otherwise
     *
     * @throws Twig_Error_Loader When $name is not found
     */
    public function isFresh($name, $time)
    {
        $info = $this->cr->getInfo($this->getFile($name));
        return $info->getLastModified()->getTimestamp() > $time;
    }
}
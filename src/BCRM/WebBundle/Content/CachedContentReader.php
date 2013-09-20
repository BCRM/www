<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Content;

use BCRM\WebBundle\Exception\InvalidArgumentException;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;

class CachedContentReader implements ContentReader
{
    const PROPERTIES_MATCH = '/@([a-z0-9]+)=([^\n]+)\n/';

    /**
     * @var \SplFileInfo
     */
    private $contentDir;

    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    private $cache;

    /**
     * @var \Knp\Bundle\MarkdownBundle\MarkdownParserInterface
     */
    private $parser;

    /**
     * @var string
     */
    private $contentPath;

    public function __construct($contentDir, $contentPath, Cache $cache, MarkdownParserInterface $parser)
    {
        $this->contentDir  = new \SplFileInfo($contentDir);
        $this->contentPath = $contentPath;
        $this->cache       = $cache;
        $this->parser      = $parser;
        $this->properties  = new  ArrayCollection();
    }

    /**
     * @param string $page
     *
     * @return Page
     */
    public function getPage($page)
    {
        return $this->buildPage($page);
    }

    protected function buildPage($page, $fetchSubNav = true)
    {
        $contentdir = $this->contentDir->getPathname() . DIRECTORY_SEPARATOR;
        $p          = new Page();
        if (!$this->cache->contains($page)) {
            $file                   = $contentdir . $page . '.md';
            $markdown               = file_get_contents($file);
            $cacheEntry             = new \stdClass();
            $cacheEntry->properties = $this->readProperties($markdown);
            $cacheEntry->subnav     = $this->getSubnav(new \SplFileInfo($file));
            $markdown               = $this->removeProperties($markdown);
            $html                   = $this->parser->transformMarkdown($markdown);
            $html                   = $this->fixLinks($html, $page);
            $cacheEntry->html       = $html;
            $this->cache->save($page, $cacheEntry, 86400);
        }
        $cacheEntry = $this->cache->fetch($page);
        $p->setContent($cacheEntry->html);
        $p->setProperties(new ArrayCollection($cacheEntry->properties));
        if ($fetchSubNav) {
            $subnav = array();
            foreach ($cacheEntry->subnav as $subpage) {
                $s = $this->buildPage($subpage, false);
                $n = new Nav();
                $n->setTitle($s->getProperties()->get('title'));
                $n->setPath($subpage);
                $subnav[]      = $n;
                $subnavOrder[] = (int)$s->getProperties()->get('order');
            }
            array_multisort($subnavOrder, SORT_ASC, $subnav);
            $p->setSubnav(new ArrayCollection($subnav));
        }
        return $p;
    }

    protected function readProperties($markdown)
    {
        $properties = array();
        if (!preg_match_all(static::PROPERTIES_MATCH, $markdown, $matches, PREG_SET_ORDER)) return $properties;
        foreach ($matches as $match) {
            $properties[$match[1]] = $match[2];
        }
        return $properties;
    }

    protected function getSubnav(\SplFileInfo $file)
    {
        $dir        = dirname($file->getPathname()) . DIRECTORY_SEPARATOR;
        $contentdir = $this->contentDir->getPathname() . DIRECTORY_SEPARATOR;
        return array_map(function ($entry) use ($contentdir) {
            $entry = str_replace($contentdir, '', $entry);
            $entry = str_replace('.md', '', $entry);
            return $entry;
        }, glob($dir . '*.md'));
    }

    protected function removeProperties($markdown)
    {
        return preg_replace(static::PROPERTIES_MATCH, '', $markdown);
    }

    protected function fixLinks($html, $page)
    {
        if (!preg_match_all('/src="([^"]+)"/', $html, $matches, PREG_SET_ORDER)) return $html;
        $path = $this->contentPath . '/' . dirname($page) . '/';
        foreach ($matches as $match) {
            $srcpath = $path . $match[1];
            $html    = str_replace($match[1], $srcpath, $html);
        }
        return $html;
    }
}
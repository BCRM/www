<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Content;

use Doctrine\Common\Collections\ArrayCollection;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;

class FileContentReader implements ContentReader
{
    const PROPERTIES_MATCH = '/@([a-z0-9]+)=([^\n]+)\n/';

    /**
     * @var \SplFileInfo
     */
    private $contentDir;

    /**
     * @var \Knp\Bundle\MarkdownBundle\MarkdownParserInterface
     */
    private $parser;

    /**
     * @var string
     */
    private $contentPath;

    public function __construct($contentDir, $contentPath, MarkdownParserInterface $parser)
    {
        $this->contentDir  = new \SplFileInfo($contentDir);
        $this->contentPath = $contentPath;
        $this->parser      = $parser;
        $this->properties  = new  ArrayCollection();
    }

    /**
     * Build content information for the given page.
     *
     * @return Info
     */
    public function getInfo($page)
    {
        $file = $this->getFilePath($page);
        $info = new Info();
        $info->setLastModified(new \DateTime('@' . filemtime($file)));
        $info->setEtag(md5_file($file));
        return $info;
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

    /**
     * @param $page
     *
     * @return \SplFileInfo
     */
    protected function getFilePath($page)
    {
        $contentdir = $this->contentDir->getPathname() . DIRECTORY_SEPARATOR;
        $file       = $contentdir . $page . '.md';
        return new \SplFileInfo($file);
    }

    protected function buildPage($page, $fetchSubNav = true)
    {
        $file       = $this->getFilePath($page);
        $p          = new Page();
        $markdown   = file_get_contents($file->getPathname());
        $properties = $this->readProperties($markdown);
        $markdown   = $this->removeProperties($markdown);
        $html       = $this->parser->transformMarkdown($markdown);
        $html       = $this->fixLinks($html, $page);
        $p->setContent($html);
        $p->setProperties(new ArrayCollection($properties));
        if ($fetchSubNav) {
            $subnav = array();
            foreach ($this->getSubnav($file) as $subpage) {
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
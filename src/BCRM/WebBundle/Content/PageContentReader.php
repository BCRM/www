<?php

/**
 * @author    Markus Tacker <m@coderbyheart.com>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Content;

use BCRM\BackendBundle\Content\Content;
use BCRM\BackendBundle\Content\FileContentReader;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;

class PageContentReader extends FileContentReader implements ContentReader
{
    /**
     * @var \Knp\Bundle\MarkdownBundle\MarkdownParserInterface
     */
    private $parser;

    public function __construct($contentDir, $contentPath, MarkdownParserInterface $parser)
    {
        parent::__construct($contentDir, $contentPath);
        $this->parser = $parser;
    }

    /**
     * @param string $path
     *
     * @return Page
     */
    public function getPage($path)
    {
        return $this->buildPage($path);
    }

    /**
     * @param string $path
     * @param bool   $fetchSubNav
     *
     * @return Page
     */
    protected function buildPage($path, $fetchSubNav = true)
    {
        $p = $this->transformContent(parent::getContent($path), $path);
        if ($fetchSubNav) {
            $subNav = array();
            foreach ($this->getSubnav($this->getFilePath($path)) as $subPage) {
                $s = $this->buildPage($subPage, false);
                if ($s->isHidden()) continue;
                $n = new Nav();
                $n->setTitle($s->getProperties()->get('title'));
                $n->setPath(str_replace('.md', '', $subPage));
                $subNav[]      = $n;
                $subNavOrder[] = (int)$s->getProperties()->get('order');
            }
            array_multisort($subNavOrder, SORT_ASC, $subNav);
            $p->setSubnav(new ArrayCollection($subNav));
        }
        return $p;
    }

    /**
     * @param Content $c
     * @param string  $path
     *
     * @return Page
     */
    protected function transformContent(Content $c, $path)
    {
        $markdown = $c->getContent();
        $markdown = $this->removeProperties($markdown);
        $html     = $this->parser->transformMarkdown($markdown);
        $html     = $this->fixLinks($html, $path);
        $page     = new Page();
        $page->setContent($html);
        $page->setProperties(new ArrayCollection(array_merge($page->getProperties()->toArray(), $c->getProperties()->toArray())));
        return $page;
    }

    protected function fixLinks($html, $page)
    {
        if (!preg_match_all('/src="([^"]+)"/', $html, $matches, PREG_SET_ORDER)) return $html;
        $path = $this->contentPath . '/' . dirname($page) . '/';
        foreach ($matches as $match) {
            $srcpath = $this->normalizePath($path . $match[1]);
            $html    = str_replace($match[1], $srcpath, $html);
        }
        return $html;
    }

    /**
     * @param $path
     *
     * @return string
     * @see http://www.php.net/manual/en/function.realpath.php#81935
     */
    protected function normalizePath($path)
    {
        $out = array();
        foreach (explode('/', $path) as $i => $fold) {
            if ($fold == '' || $fold == '.') continue;
            if ($fold == '..' && $i > 0 && end($out) != '..') array_pop($out);
            else $out[] = $fold;
        }
        return ($path{0} == '/' ? '/' : '') . join('/', $out);
    }

    protected function getSubnav(\SplFileInfo $file)
    {
        $dir        = dirname($file->getPathname()) . DIRECTORY_SEPARATOR;
        $contentdir = $this->contentDir->getPathname() . DIRECTORY_SEPARATOR;
        return array_map(function ($entry) use ($contentdir) {
            $entry = str_replace($contentdir, '', $entry);
            return $entry;
        }, glob($dir . '*.md'));
    }
}

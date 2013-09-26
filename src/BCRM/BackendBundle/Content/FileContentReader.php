<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Content;

use BCRM\BackendBundle\Exception\FileNotFoundException;
use Doctrine\Common\Collections\ArrayCollection;

class FileContentReader implements ContentReader
{
    const PROPERTIES_MATCH = '/@([a-z0-9]+)=([^\n]+)\n/';

    /**
     * @var \SplFileInfo
     */
    protected $contentDir;

    /**
     * @var string
     */
    protected $contentPath;

    public function __construct($contentDir, $contentPath)
    {
        $this->contentDir  = new \SplFileInfo($contentDir);
        $this->contentPath = $contentPath;
        $this->properties  = new  ArrayCollection();
    }

    /**
     * {@inheritDocs}
     */
    public function getInfo($path)
    {
        $file = $this->getFilePath($path);
        $info = new Info();
        $info->setLastModified(new \DateTime('@' . filemtime($file)));
        $info->setEtag(md5_file($file));
        return $info;
    }

    /**
     * @param $path
     *
     * @return \SplFileInfo
     */
    protected function getFilePath($path)
    {
        $contentdir = $this->contentDir->getPathname() . DIRECTORY_SEPARATOR;
        $file       = $contentdir . $path;
        if (!is_file($file)) throw new FileNotFoundException($path);
        return new \SplFileInfo($file);
    }

    /**
     * {@inheritDocs}
     */
    public function getContent($path)
    {
        $file       = $this->getFilePath($path);
        $content    = file_get_contents($file->getPathname());
        $properties = $this->readProperties($content);
        $content    = $this->removeProperties($content);
        $c          = new Content();
        $c->setContent($content);
        $c->setProperties(new ArrayCollection(array_merge($c->getProperties()->toArray(), $properties)));
        return $c;
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

    protected function removeProperties($markdown)
    {
        return preg_replace(static::PROPERTIES_MATCH, '', $markdown);
    }
}

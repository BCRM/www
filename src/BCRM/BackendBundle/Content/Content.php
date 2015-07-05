<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2015 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Content;

use Doctrine\Common\Collections\ArrayCollection;

class Content
{
    /**
     * @var string
     */
    protected $content;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $properties;

    public function __construct()
    {
        $this->properties = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $properties
     */
    public function setProperties(ArrayCollection $properties)
    {
        $this->properties = $properties;
    }
}

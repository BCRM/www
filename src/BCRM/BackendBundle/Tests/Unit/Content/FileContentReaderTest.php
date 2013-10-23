<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Tests\Unit\Content;

class FileContentReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @group unit
     */
    public function etagShouldChange()
    {
        $tempfile = new \SplFileInfo(tempnam(sys_get_temp_dir(), 'fcrtest'));
        $fp       = fopen($tempfile, 'a+');
        fputs($fp, 'a');
        fclose($fp);
        $fcr1  = new \BCRM\BackendBundle\Content\FileContentReader(dirname($tempfile->getPathname()), '/content');
        $info1 = $fcr1->getInfo($tempfile->getFilename());
        $fp    = fopen($tempfile, 'a+');
        fputs($fp, 'b');
        fclose($fp);
        $fcr2  = new \BCRM\BackendBundle\Content\FileContentReader(dirname($tempfile->getPathname()), '/content');
        $info2 = $fcr2->getInfo($tempfile->getFilename());
        $this->assertNotEquals($info1->getEtag(), $info2->getEtag());
    }
}

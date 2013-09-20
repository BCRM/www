<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebControllerTest extends WebTestCase
{
    /**
     * @test
     * @group functional
     */
    public function newsletterSignup()
    {
        $client        = static::createClient();
        $crawler       = $client->request('GET', '/');
        $form          = $crawler->selectButton('submit')->form();
        $form['email'] = 'name@domain.com';
        $client->submit($form);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }
}
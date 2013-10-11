<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use BCRM\BackendBundle\Entity\Newsletter\Subscription;
use BCRM\BackendBundle\Service\Newsletter\SendConfirmationMailCommand;
use Symfony\Component\Security\Core\Util\SecureRandom;

class WebControllerTest extends WebTestCase
{
    /**
     * @test
     * @group functional
     */
    public function newsletterSignup()
    {
        $email                               = 'name@domain.com';
        $client                              = static::createClient();
        $crawler                             = $client->request('GET', '/');
        $form                                = $crawler->selectButton('submit')->form();
        $form['newsletter_subscribe[email]'] = $email;
        $client->submit($form);
        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->isRedirect('/newsletter/ok'), sprintf('Unexpected redirect to %s', $response->headers->get('Location')));
        return $email;
    }

    /**
     * @test
     * @group   functional
     * @depends newsletterSignup
     */
    public function newsletterConfirm($email)
    {
        $client    = static::createClient();
        $container = $client->getContainer();

        // Load confirmation key
        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $em = $container
            ->get('doctrine')
            ->getManager();

        /* @var $subscription Subscription */
        $subscription = $em->getRepository('BCRMBackendBundle:Newsletter\Subscription')->getSubscription($email)->get();
        $sr           = new SecureRandom();
        $key          = sha1($sr->nextBytes(256), false);
        $id           = $subscription->getId();
        $subscription->setConfirmationKey($key);
        $em->persist($subscription);
        $em->flush();

        // Confirm
        $client = static::createClient();
        $client->request('GET', sprintf('/newsletter/confirm/%d/%s', $id, $key));
        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->isRedirect('/newsletter/aktiviert'), sprintf('Unexpected redirect to %s', $response->headers->get('Location')));
    }
}

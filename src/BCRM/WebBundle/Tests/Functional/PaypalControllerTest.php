<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Tests\Functional;

use BCRM\BackendBundle\Entity\Payment;

class PaypalControllerTest extends Base
{
    /**
     */
    public static function setUpBeforeClass()
    {
        static::resetDatabase();
    }

    /**
     * @test
     * @group functional
     */
    public function webhook()
    {
        $client = static::createClient();
        $data   = [
            'address_city'           => 'Freiburg',
            'address_country'        => 'Germany',
            'address_country_code'   => 'DE',
            'address_name'           => 'Demo Kunde',
            'address_state'          => 'Empty',
            'address_status'         => 'unconfirmed',
            'address_street'         => 'ESpachstr. 1',
            'address_zip'            => '79111',
            'business'               => 'netzkasse+sandbox@netzkultur-rheinmain.de',
            'charset'                => 'windows-1252',
            'custom'                 => null,
            'first_name'             => 'Demo',
            'handling_amount'        => '0.00',
            'ipn_track_id'           => 'bb805611bddb3',
            'item_name'              => 'BarCamp 2015 04./05.08.2015 Ticket für Markus Tacker gültig am Samstag',
            'item_number'            => 'df8384ed4507c4a04819cd2e3281191e5a43a3b6',
            'last_name'              => 'Kunde',
            'mc_currency'            => 'EUR',
            'mc_fee'                 => '1.06',
            'mc_gross'               => '37.50',
            'notify_version'         => '3.8',
            'payer_email'            => 'demo-kunde@barcamp-rheinmain.de',
            'payer_id'               => 'CARV38JGU7NGS',
            'payer_status'           => 'unverified',
            'payment_date'           => '08:44:55 Jul 05, 2015 PDT',
            'payment_fee'            => null,
            'payment_gross'          => null,
            'payment_status'         => 'Completed',
            'payment_type'           => 'instant',
            'protection_eligibility' => 'Eligible',
            'quantity'               => '1',
            'receiver_email'         => 'netzkasse+sandbox@netzkultur-rheinmain.de',
            'receiver_id'            => 'MADLHS6XJAZFS',
            'residence_country'      => 'DE',
            'shipping'               => '0.00',
            'tax'                    => '0.00',
            'test_ipn'               => '1',
            'transaction_subject'    => null,
            'txn_id'                 => '3F3525487J2452828',
            'txn_type'               => 'web_accept',
            'verify_sign'            => 'AoNB-1eMvfdupacZeervP-FX318PAk65Kf02iyB8668ErUUxsHFUm6sO',
        ];
        $client->request('POST', '/paypal', $data);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $container = $client->getContainer();
        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $em = $container
            ->get('doctrine')
            ->getManager();

        $payments = $em->getRepository('BCRMBackendBundle:Payment')->findAll();
        $this->assertEquals(1, count($payments));

        /** @var Payment $payment */
        $payment = $payments[0];
        $this->assertEquals('3F3525487J2452828', $payment->getTransactionId());
        $this->assertEquals($data, $payment->getPayload()->toArray());
        $this->assertEquals('paypal', $payment->getMethod());
        $this->assertFalse($payment->isVerified());
    }
}

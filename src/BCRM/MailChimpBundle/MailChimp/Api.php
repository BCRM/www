<?php
/**
 * Created by PhpStorm.
 * User: m
 * Date: 03.11.13
 * Time: 10:50
 */

namespace BCRM\MailChimpBundle\MailChimp;

use BCRM\MailChimpBundle\Exception\BadMethodCallException;
use Buzz\Browser;
use Buzz\Client\Curl;

class Api
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $dataCenter;

    /**
     * @var string
     */
    private $format = 'json';

    public function __construct($apiKey)
    {
        if (!preg_match('/^[0-9a-f]+-[0-9a-z]+$/', $apiKey)) {
            throw new BadMethodCallException(sprintf('Api key "%s" has invalid format.', $apiKey));
        }
        list($a, $this->dataCenter) = explode('-', $apiKey);
        $this->apiKey = $apiKey;
    }

    protected function get($endpoint)
    {
        $request      = array(
            'apikey' => $this->apiKey,
        );
        $requestData  = json_encode($request);
        $browser      = new Browser(new Curl());
        $response     = $browser->post(
            sprintf('https://%s.api.mailchimp.com/2.0/%s.%s', $this->dataCenter, $endpoint, $this->format),
            array(
                //'Content-Type: application/x-www-form-urlencoded',
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($requestData),
                'User-Agent: BCRMMailChimpBundle',
                'Accept: application/json'
            ),
            $requestData
        );
        $responseData = json_decode($response->getContent());
        return $responseData;
    }

    public function __call($method, $args)
    {
        if (!preg_match('/([a-z]+)([A-Z][a-z]+)$/', $method, $matches)) {
            $this->$method($args);
        }
        $result = $this->get(strtolower($matches[1] . '/' . $matches[2]), $args);
        return $result->data;
    }
} 
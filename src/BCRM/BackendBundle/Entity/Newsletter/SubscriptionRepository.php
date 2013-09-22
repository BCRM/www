<?php
/**
 * Created by JetBrains PhpStorm.
 * User: m
 * Date: 22.09.13
 * Time: 19:39
 * To change this template use File | Settings | File Templates.
 */

namespace BCRM\BackendBundle\Entity\Newsletter;


interface SubscriptionRepository
{
    /**
     * @param $email
     *
     * @return \PhpOption\Option
     */
    public function getSubscription($email);
}
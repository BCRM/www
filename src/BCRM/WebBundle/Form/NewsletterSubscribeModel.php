<?php
/**
 * Created by JetBrains PhpStorm.
 * User: m
 * Date: 22.09.13
 * Time: 17:46
 * To change this template use File | Settings | File Templates.
 */

namespace BCRM\WebBundle\Form;

use Symfony\Component\Validator\Constraints as Assert;

class NewsletterSubscribeModel
{
    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Email
     */
    public $email;

    /**
     * @var bool
     * @Assert\Type(type="boolean")
     */
    public $futurebarcamps = false;
}
<?php

namespace BCRM\BackendBundle\Service\Mail;

class SendTemplateMailCommand
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $template;

    /**
     * @var array
     */
    public $templateData;

    /**
     * @var string
     */
    public $image;

    /**
     * @var string
     */
    public $format = 'text/plain';
}

<?php

namespace BCRM\BackendBundle\Service;

use BCRM\BackendBundle\Service\Mail\SendTemplateMailCommand;
use BCRM\BackendBundle\Content\ContentReader;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\Templating\TemplateReference;

class Mail
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \Symfony\Bridge\Twig\TwigEngine
     */
    private $templating;

    /**
     * @var string
     */
    private $mailFromName;

    /**
     * @var string
     */
    private $mailFromEmail;

    /**
     * @var \BCRM\BackendBundle\Content\ContentReader
     */
    private $cr;

    /**
     * @param \Swift_Mailer $mailer
     * @param               $mailFrom
     */
    public function __construct(\Swift_Mailer $mailer, TwigEngine $templating, $mailFromEmail, $mailFromName, ContentReader $cr)
    {
        $this->mailer        = $mailer;
        $this->templating    = $templating;
        $this->mailFromEmail = $mailFromEmail;
        $this->mailFromName  = $mailFromName;
        $this->cr            = $cr;
    }

    public function sendTemplateMail(SendTemplateMailCommand $command)
    {
        $tplIdentifier = 'Email/' . $command->template . '.txt';
        $template      = $this->cr->getContent($tplIdentifier);
        $subject       = $template->getProperties()->containsKey('subject') ? $template->getProperties()->get('subject') : $this->mailFromName;

        $message = \Swift_Message::newInstance();
        $message->setCharset('UTF-8');
        $message->setFrom($this->mailFromEmail, $this->mailFromName)
            ->setSubject($subject)
            ->setTo($command->email)
            ->setBody(
                $this->templating->render('bcrm_content:' . $tplIdentifier, $command->templateData)
            );
        $this->mailer->send($message);
    }
}

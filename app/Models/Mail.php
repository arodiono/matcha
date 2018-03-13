<?php

namespace App\Models;
use App\Services\EmailService;


class Mail
{
    protected $view;

    protected $mailer;

    public function __construct($view, $mailer)
    {
        $this->view = $view;
        $this->mailer = $mailer;
    }

    public function send($template, $data, $callback)
    {
        $message = new EmailService($this->mailer);

        $message->body($this->view->fetch($template, $data));

        call_user_func($callback, $message);

        $this->mailer->send();
    }
}
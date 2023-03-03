<?php

namespace App\service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class Mailerservice
{
    public function __construct(private MailerInterface $mailer) {

    }
    public function sendEmail(): void
    {
        $email = (new Email())
            ->from('ziedi.imene@esprit.tn')
            ->to('wissem.benhouria@esprit.tn')
            ->subject('mail from symfony')
//            ->text('Sending emails is fun again!')
            ->html('<p>test mail</p>');
        $this->mailer->send($email);
        // ...
    }

}
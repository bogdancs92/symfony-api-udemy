<?php


namespace App\Email;


use App\Entity\User;

class Mailer
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    private $twig;

    public function __construct(\Swift_Mailer $mailer, \Twig\Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendConfirmation(User $user) {
        $body = $this->twig->render('email/confirmation.html.twig',
            [
                'user' => $user
            ]
        );
        $message = (new \Swift_Message("Please confirm your account"))
            ->setFrom('drscanteie@gmail.c om')
            ->setTo($user->getEmail())
            ->setBody($body,'text/html');
        echo($body);
        $this->mailer->send($message);
    }
}
<?php

namespace App\Service;

use App\Entity\Users;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

final class AuthEmailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly LoggerInterface $logger,
        private readonly string $fromAddress,
    ) {
    }

    public function sendPasswordReset(Users $user, string $resetUrl): void
    {
        try {
            $email = (new Email())
                ->from(Address::create($this->fromAddress))
                ->to($user->getEmail())
                ->subject('After Travel — Réinitialisation de votre mot de passe')
                ->html($this->twig->render('emails/reset_password.html.twig', [
                    'user' => $user,
                    'resetUrl' => $resetUrl,
                ]));

            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->warning('Email reset mot de passe non envoyé : ' . $e->getMessage());
        }
    }
}

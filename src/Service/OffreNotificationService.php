<?php

namespace App\Service;

use App\Entity\Offre;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Handles all offer-related notifications:
 *   1. Admin notified when an offer has expired (dateFin passed)
 *   2. Users notified when an offer price drops
 *   3. Users notified when a new offer is added
 *
 * Called from:
 *   - OffreController::new()   → triggerNewOffreNotification()
 *   - OffreController::edit()  → triggerPriceDropNotification()
 *   - A scheduled command      → checkExpiredOffres()
 */
class OffreNotificationService
{
    public function __construct(
        private readonly MailerInterface        $mailer,
        private readonly EntityManagerInterface $em,
        private readonly string                 $adminEmail,   // injected via services.yaml
        private readonly string                 $appBaseUrl,   // APP_BASE_URL from .env
    ) {}

    // ─────────────────────────────────────────────────────────────────
    // 1. Notify admin of expired offers (call this from a console Command)
    // ─────────────────────────────────────────────────────────────────
    public function checkExpiredOffres(): int
    {
        $today  = new \DateTime();
        $offres = $this->em->getRepository(Offre::class)->findAll();
        $count  = 0;

        foreach ($offres as $offre) {
            if (
                $offre->getDateFin() &&
                $offre->getDateFin() < $today &&
                !$offre->isNotificationEnvoyee()
            ) {
                $this->sendExpiredNotification($offre);
                $offre->setNotificationEnvoyee(true);
                $count++;
            }
        }

        $this->em->flush();
        return $count;
    }

    // ─────────────────────────────────────────────────────────────────
    // 2. Price drop – notify all active users
    // ─────────────────────────────────────────────────────────────────
    public function triggerPriceDropNotification(Offre $offre, float $oldPrice): void
    {
        if ($offre->getPrix() >= $oldPrice) {
            return;
        }

        $diff    = round($oldPrice - $offre->getPrix(), 2);
        $percent = round(($diff / $oldPrice) * 100);

        $users = $this->em->getRepository(Users::class)->findBy(['statut' => 'actif']);

        foreach ($users as $user) {
            if (!$user->getEmail()) continue;

            $email = (new Email())
                ->from('noreply@aftertravel.tn')
                ->to($user->getEmail())
                ->subject("💰 Baisse de prix : {$offre->getTitre()} (-{$percent}%)")
                ->html($this->buildPriceDropHtml($offre, $oldPrice, $diff, $percent));

            try {
                $this->mailer->send($email);
            } catch (\Throwable) {
                // Silent fail per user
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // 3. New offer – notify all active users
    // ─────────────────────────────────────────────────────────────────
    public function triggerNewOffreNotification(Offre $offre): void
    {
        $users = $this->em->getRepository(Users::class)->findBy(['statut' => 'actif']);

        foreach ($users as $user) {
            if (!$user->getEmail()) continue;

            $email = (new Email())
                ->from('noreply@aftertravel.tn')
                ->to($user->getEmail())
                ->subject("🌟 Nouvelle offre disponible : {$offre->getTitre()}")
                ->html($this->buildNewOffreHtml($offre));

            try {
                $this->mailer->send($email);
            } catch (\Throwable) {}
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────
    private function sendExpiredNotification(Offre $offre): void
    {
        $email = (new Email())
            ->from('noreply@aftertravel.tn')
            ->to($this->adminEmail)
            ->subject("⚠️ Offre expirée : {$offre->getTitre()}")
            ->html("
                <h2>⚠️ Offre expirée</h2>
                <p>L'offre <strong>{$offre->getTitre()}</strong> (ID {$offre->getId()}) a expiré le 
                   {$offre->getDateFin()->format('d/m/Y')}.</p>
                <p>Prix : {$offre->getPrix()} €</p>
                <a href='{$this->appBaseUrl}/offre/{$offre->getId()}/edit' 
                   style='background:#1a3a6e;color:white;padding:10px 20px;
                          text-decoration:none;border-radius:6px;'>
                   Gérer l'offre
                </a>
            ");

        try {
            $this->mailer->send($email);
        } catch (\Throwable) {}
    }

    private function buildPriceDropHtml(Offre $o, float $old, float $diff, int $pct): string
    {
        $url = "{$this->appBaseUrl}/offre/{$o->getId()}";
        return "
            <div style='font-family:sans-serif;max-width:600px;margin:auto;'>
              <div style='background:#1a3a6e;padding:24px;border-radius:12px 12px 0 0;text-align:center;'>
                <h1 style='color:white;margin:0;'>💰 Baisse de prix !</h1>
              </div>
              <div style='background:#f8f9fa;padding:24px;border-radius:0 0 12px 12px;'>
                <h2 style='color:#1a3a6e;'>{$o->getTitre()}</h2>
                <p>Le prix de cette offre a baissé de <strong style='color:#dc3545;'>{$diff} € (-{$pct}%)</strong></p>
                <p>
                  <span style='text-decoration:line-through;color:#aaa;'>{$old} €</span>
                  &nbsp;→&nbsp;
                  <strong style='color:#198754;font-size:1.4em;'>{$o->getPrix()} €</strong>
                </p>
                <p>Durée : {$o->getDuree()} jours · Destination : " . ($o->getDestination() ?? 'N/A') . "</p>
                <a href='{$url}' style='display:inline-block;background:#1a3a6e;color:white;
                   padding:12px 28px;text-decoration:none;border-radius:8px;margin-top:12px;'>
                   Voir l'offre
                </a>
              </div>
            </div>
        ";
    }

    private function buildNewOffreHtml(Offre $o): string
    {
        $url = "{$this->appBaseUrl}/offre/{$o->getId()}";
        return "
            <div style='font-family:sans-serif;max-width:600px;margin:auto;'>
              <div style='background:#1a3a6e;padding:24px;border-radius:12px 12px 0 0;text-align:center;'>
                <h1 style='color:white;margin:0;'>🌟 Nouvelle offre</h1>
              </div>
              <div style='background:#f8f9fa;padding:24px;border-radius:0 0 12px 12px;'>
                <h2 style='color:#1a3a6e;'>{$o->getTitre()}</h2>
                <p>Un nouveau voyage vous attend !</p>
                <ul style='color:#444;'>
                  <li><strong>Prix :</strong> {$o->getPrix()} €</li>
                  <li><strong>Durée :</strong> {$o->getDuree()} jours</li>
                  <li><strong>Destination :</strong> " . ($o->getDestination() ?? 'À découvrir') . "</li>
                  <li><strong>Service :</strong> {$o->getService()->getNomService()}</li>
                </ul>
                <a href='{$url}' style='display:inline-block;background:#1a3a6e;color:white;
                   padding:12px 28px;text-decoration:none;border-radius:8px;margin-top:12px;'>
                   Découvrir l'offre
                </a>
              </div>
            </div>
        ";
    }
}
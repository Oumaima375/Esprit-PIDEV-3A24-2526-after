<?php

namespace App\Service;

class MailjetService
{
    public function __construct(
        private string $apiKey,
        private string $apiSecret
    ) {}

    public function envoyerNotificationExpiration(
        string $emailDestinataire,
        string $nomDocument,
        string $dateExpiration
    ): bool {
        $mj = new \Mailjet\Client(
            $this->apiKey,
            $this->apiSecret,
            true,
            ['version' => 'v3.1']
        );

        $body = [
            'Messages' => [[
                'From' => [
                    'Email' => 'noreply@aftertravel.com',
                    'Name'  => 'AFTER Travel'
                ],
                'To' => [[
                    'Email' => $emailDestinataire,
                    'Name'  => 'Voyageur'
                ]],
                'Subject'  => '⚠️ Document expire bientôt — ' . $nomDocument,
                'HTMLPart' => '
                    <div style="font-family:Arial; max-width:600px; margin:auto;">
                        <div style="background:linear-gradient(135deg,#1a2744,#2563eb); padding:30px; text-align:center;">
                            <h1 style="color:white; margin:0;">✈ AFTER Travel</h1>
                        </div>
                        <div style="padding:30px; background:#f8fafc;">
                            <h2 style="color:#1a2744;">⚠️ Document expire bientôt</h2>
                            <p>Votre document <strong>' . $nomDocument . '</strong> expire le <strong style="color:#ef4444;">' . $dateExpiration . '</strong>.</p>
                            <p>Pensez à le renouveler avant la date d\'expiration !</p>
                            <a href="http://127.0.0.1:8000/document/crud"
                               style="background:#2563eb; color:white; padding:12px 25px; border-radius:8px; text-decoration:none; font-weight:bold;">
                                Gérer mes documents
                            </a>
                        </div>
                        <div style="padding:15px; text-align:center; color:#94a3b8; font-size:12px;">
                            © 2026 AFTER Travel
                        </div>
                    </div>
                '
            ]]
        ];

        $response = $mj->post(\Mailjet\Resources::$Email, ['body' => $body]);
        return $response->success();
    }
}
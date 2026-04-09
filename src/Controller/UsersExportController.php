<?php

namespace App\Controller;

use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/users')]
#[IsGranted('ROLE_ADMIN')]
class UsersExportController extends AbstractController
{
    // ─── EXPORT CSV ──────────────────────────────────────────────────────────

    #[Route('/export/csv', name: 'users_export_csv', methods: ['GET'])]
    public function exportCsv(UsersRepository $repo): StreamedResponse
    {
        $users = $repo->findAll();

        $response = new StreamedResponse(function () use ($users) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($handle, [
                'ID',
                'Nom',
                'Prénom',
                'Email',
                'Téléphone',
                'Type utilisateur',
                'Vérifié',
                'Expiry token',
            ], ';');

            // Data rows
            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->getId(),
                    $user->getNom(),
                    $user->getPrenom(),
                    $user->getEmail(),
                    $user->getTelephone() ?? '',
                    $user->getTypeUtilisateur(),
                    $user->isVerified() ? 'Oui' : 'Non',
                    $user->getVerificationExpiry()
                        ? $user->getVerificationExpiry()->format('d/m/Y H:i')
                        : '',
                ], ';');
            }

            fclose($handle);
        });

        $filename = 'utilisateurs_' . date('Ymd_His') . '.csv';

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    // ─── EXPORT PDF ──────────────────────────────────────────────────────────

    #[Route('/export/pdf', name: 'users_export_pdf', methods: ['GET'])]
    public function exportPdf(UsersRepository $repo): Response
    {
        $users = $repo->findAll();

        // Build HTML for the PDF
        $html = $this->buildPdfHtml($users);

        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isFontSubsettingEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'utilisateurs_' . date('Ymd_His') . '.pdf';

        $output = $dompdf->output();

        return new Response($output, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length'      => strlen($output),
        ]);
    }

    // ─── HTML BUILDER FOR PDF ────────────────────────────────────────────────

    private function buildPdfHtml(array $users): string
    {
        $totalUsers    = count($users);
        $totalAdmins   = count(array_filter($users, fn($u) => strtolower($u->getTypeUtilisateur()) === 'admin'));
        $totalVoyageurs = count(array_filter($users, fn($u) => strtolower($u->getTypeUtilisateur()) === 'voyageur'));
        $generatedAt   = date('d/m/Y à H:i');

        // Build table rows
        $rows = '';
        foreach ($users as $i => $user) {
            $bgRow    = $i % 2 === 0 ? '#ffffff' : '#f8f9fc';
            $verified = $user->isVerified()
                ? '<span style="background:#dcfce7;color:#15803d;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;">OUI</span>'
                : '<span style="background:#fee2e2;color:#b91c1c;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;">NON</span>';

            $roleColor = match (strtolower($user->getTypeUtilisateur())) {
                'admin'    => 'background:#dbeafe;color:#1d4ed8;',
                'voyageur' => 'background:#dcfce7;color:#15803d;',
                default    => 'background:#fef9c3;color:#92400e;',
            };

            $roleBadge = sprintf(
                '<span style="%spadding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;">%s</span>',
                $roleColor,
                htmlspecialchars($user->getTypeUtilisateur())
            );

            $expiry = $user->getVerificationExpiry()
                ? $user->getVerificationExpiry()->format('d/m/Y')
                : '-';

            $rows .= sprintf('
                <tr style="background:%s;">
                    <td style="padding:10px 14px;border-bottom:1px solid #e2e5f0;color:#6b7280;font-size:12px;">%d</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #e2e5f0;font-weight:600;color:#1a2b5e;">%s %s</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #e2e5f0;color:#374151;font-size:13px;">%s</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #e2e5f0;color:#374151;">%s</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #e2e5f0;text-align:center;">%s</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #e2e5f0;text-align:center;">%s</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #e2e5f0;color:#6b7280;font-size:12px;text-align:center;">%s</td>
                </tr>',
                $bgRow,
                $user->getId(),
                htmlspecialchars($user->getPrenom()),
                htmlspecialchars($user->getNom()),
                htmlspecialchars($user->getEmail()),
                htmlspecialchars($user->getTelephone() ?? '-'),
                $roleBadge,
                $verified,
                $expiry
            );
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export Utilisateurs</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            background: #f8f9fc;
            color: #1a2b5e;
            font-size: 13px;
        }
        .page { padding: 32px 36px; }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e5f0;
        }
        .brand { font-size: 22px; font-weight: 900; color: #1a2b5e; letter-spacing: -0.5px; }
        .brand span { color: #3b5bdb; }
        .meta { text-align: right; font-size: 11px; color: #9ca3af; line-height: 1.7; }
        .meta strong { color: #6b7280; }

        /* Stats */
        .stats {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-box {
            flex: 1;
            background: #ffffff;
            border-radius: 10px;
            padding: 16px 20px;
            border: 1px solid #e2e5f0;
            text-align: center;
        }
        .stat-label { font-size: 11px; color: #9ca3af; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-value { font-size: 32px; font-weight: 900; color: #3b5bdb; line-height: 1; }

        /* Table */
        .table-wrapper {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e5f0;
            overflow: hidden;
        }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #1a2b5e; }
        thead th {
            padding: 12px 14px;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            text-align: left;
        }
        thead th.center { text-align: center; }

        /* Footer */
        .footer {
            margin-top: 24px;
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
<div class="page">

    <!-- En-tête -->
    <div class="header">
        <div>
            <div class="brand">After <span>Travel</span></div>
            <div style="font-size:17px;font-weight:700;color:#374151;margin-top:4px;">Rapport — Gestion des utilisateurs</div>
        </div>
        <div class="meta">
            <strong>Généré le :</strong> {$generatedAt}<br>
            <strong>Total exporté :</strong> {$totalUsers} utilisateur(s)
        </div>
    </div>

    <!-- Statistiques -->
    <div class="stats">
        <div class="stat-box">
            <div class="stat-label">Total utilisateurs</div>
            <div class="stat-value">{$totalUsers}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Administrateurs</div>
            <div class="stat-value">{$totalAdmins}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Voyageurs</div>
            <div class="stat-value">{$totalVoyageurs}</div>
        </div>
    </div>

    <!-- Tableau -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom complet</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th class="center">Rôle</th>
                    <th class="center">Vérifié</th>
                    <th class="center">Expiry</th>
                </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        After Travel Administration · Export automatique · {$generatedAt}
    </div>

</div>
</body>
</html>
HTML;
    }
}
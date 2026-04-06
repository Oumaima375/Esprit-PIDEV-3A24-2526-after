<?php
namespace App\Service;
use Dompdf\Dompdf;
use Dompdf\Options;
class PdfService
{
 public function generateVoyagePdf(array $data): string
 {
 $options = new Options();
 $options->set('defaultFont', 'Arial');
 $dompdf = new Dompdf($options);
 $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">
 <style>
 body { font-family: Arial, sans-serif; margin:0; padding:20px; }
 .header { background:#13357B; color:white; padding:30px; text-align:center; }
 .header h1 { margin:0; font-size:28px; }
 table { width:100%; border-collapse:collapse; margin-top:20px; }
 td { padding:10px; border-bottom:1px solid #eee; }
 td:first-child { font-weight:bold; color:#13357B; width:40%; }
 .footer { text-align:center; margin-top:30px; font-size:10px; color:#888; }
 </style></head><body>
 <div class="header"><h1>After Travel</h1><p>Votre passeport vers l aventure</p></div>
 <div style="padding:30px;">
 <h2 style="color:#13357B;">' . htmlspecialchars($data['titre']) . '</h2>
 <p>' . htmlspecialchars($data['description']) . '</p>
 <table>
 <tr><td>Date debut</td><td>' . $data['date_debut'] . '</td></tr>
 <tr><td>Date fin</td><td>' . $data['date_fin'] . '</td></tr>
 <tr><td>Prix</td><td>' . $data['prix'] . ' TND</td></tr>
 <tr><td>Places</td><td>' . $data['nb_places'] . '</td></tr>
 <tr><td>Destination</td><td>' . $data['destination'] . '</td></tr>
 </table>
 <div class="footer">Genere par After Travel — ' . date('d/m/Y H:i') . '</div>
 </div></body></html>';
 $dompdf->loadHtml($html);
 $dompdf->setPaper('A4', 'portrait');
 $dompdf->render();
 return $dompdf->output();
 }
}

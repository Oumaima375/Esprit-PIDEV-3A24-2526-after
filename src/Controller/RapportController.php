<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RapportController extends AbstractController
{
    private array $moisNoms = [
        'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre',
    ];

    private array $colors = [
        '#13357B', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6',
        '#1abc9c', '#e67e22', '#3498db', '#e91e63', '#00bcd4',
        '#8bc34a', '#ff5722',
    ];

    #[Route('/rapport', name: 'rapport_index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();
        $filterData = $this->buildFilterData($request);

        $depenses = $conn->executeQuery($filterData['sql'], $filterData['params'])->fetchAllAssociative();
        $categories = $conn->executeQuery('SELECT id_cat, nom_categorie FROM categorie')->fetchAllAssociative();

        [$totalGeneral, $totalNb, $donneesCategories] = $this->calculerDonnees($depenses, $categories);
        $pieData = $this->buildPieData($donneesCategories, $totalGeneral);
        $pdfQueryString = http_build_query($request->query->all());

        $viewParams = [
            'totalGeneral'    => $totalGeneral,
            'totalNb'         => $totalNb,
            'donneesCategories' => $donneesCategories,
            'pieData'         => $pieData,
            'filter_type'     => $filterData['filter_type'],
            'resumeFiltre'    => $filterData['label'],
            'mois'            => $filterData['mois'],
            'annee'           => $filterData['annee'],
            'currentYear'     => $filterData['currentYear'],
            'moisMultiple'    => $filterData['moisMultiple'],
            'anneeMultiple'   => $filterData['anneeMultiple'],
            'date_debut'      => $filterData['date_debut'],
            'date_fin'        => $filterData['date_fin'],
            'pdfQueryString'  => $pdfQueryString,
        ];

        if ($request->isXmlHttpRequest()) {
            return $this->render('rapport/_content.html.twig', $viewParams);
        }
        return $this->render('rapport/index.html.twig', $viewParams);
    }

    #[Route('/rapport/pdf', name: 'rapport_pdf')]
    public function pdf(Request $request, EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();
        $filterData = $this->buildFilterData($request);

        $depenses = $conn->executeQuery($filterData['sql'], $filterData['params'])->fetchAllAssociative();
        $categories = $conn->executeQuery('SELECT id_cat, nom_categorie FROM categorie')->fetchAllAssociative();

        [$totalGeneral, $totalNb, $donneesCategories] = $this->calculerDonnees($depenses, $categories);
        $pieData = $this->buildPieData($donneesCategories, $totalGeneral);

        $html = $this->renderView('rapport/pdf.html.twig', [
            'totalGeneral'    => $totalGeneral,
            'totalNb'         => $totalNb,
            'donneesCategories' => $donneesCategories,
            'pieData'         => $pieData,
            'periodeTexte'    => $filterData['label'],
            'dateGeneration'  => date('d/m/Y H:i'),
        ]);

        return new Response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    private function buildPieData(array $donneesCategories, float $totalGeneral): array
    {
        if ($totalGeneral <= 0 || empty($donneesCategories)) return [];

        $segments = [];
        $startAngle = -M_PI / 2;
        $cx = 150; $cy = 150; $r = 100;
        $i = 0;

        foreach ($donneesCategories as $cat => $data) {
            $montant = (float) $data[0];
            $percentage = $montant / $totalGeneral * 100;
            $angle = $montant / $totalGeneral * 2 * M_PI;
            $endAngle = $startAngle + $angle;

            $segments[] = [
                'color'      => $this->colors[$i % count($this->colors)],
                'category'   => $cat,
                'percentage' => $percentage,
                'x1'         => round($cx + $r * cos($startAngle), 4),
                'y1'         => round($cy + $r * sin($startAngle), 4),
                'x2'         => round($cx + $r * cos($endAngle), 4),
                'y2'         => round($cy + $r * sin($endAngle), 4),
                'largeArc'   => $angle > M_PI ? 1 : 0,
            ];

            $startAngle = $endAngle;
            $i++;
        }
        return $segments;
    }

    private function buildFilterData(Request $request): array
    {
        $filterType = $request->query->get('filter_type', 'simple');
        $sql = 'SELECT * FROM depense WHERE 1=1';
        $params = [];
        $label = 'Toutes les dépenses';
        $currentYear = (int) date('Y');

        $mois = $request->query->get('mois');
        $anneeRaw = $request->query->get('annee');
        $annee = ($anneeRaw !== null && $anneeRaw !== '') ? (int) $anneeRaw : null;
        $moisMultiple = array_map('intval', array_values(array_filter($request->query->all('mois_multiple'), fn($v) => $v !== '' && $v !== null)));
        $anneeMultiple = array_map('intval', array_values(array_filter($request->query->all('annee_multiple'), fn($v) => $v !== '' && $v !== null)));
        $dateDebut = (string) $request->query->get('date_debut', '');
        $dateFin = (string) $request->query->get('date_fin', '');

        if ($filterType === 'simple') {
            if ($mois && $annee) {
                $safeMonth = max(1, min(12, (int) $mois));
                $start = sprintf('%d-%02d-01', $annee, $safeMonth);
                $end   = date('Y-m-t', strtotime($start));
                $sql .= ' AND date_depense BETWEEN ? AND ?';
                $params = [$start, $end];
                $label = $this->moisNoms[$safeMonth - 1] . " $annee";
            } elseif ($annee) {
                $sql .= ' AND date_depense BETWEEN ? AND ?';
                $params = [sprintf('%d-01-01', $annee), sprintf('%d-12-31', $annee)];
                $label = "Année $annee";
            } elseif ($mois) {
                $safeMonth = max(1, min(12, (int) $mois));
                $start = sprintf('%d-%02d-01', $currentYear, $safeMonth);
                $end   = date('Y-m-t', strtotime($start));
                $sql .= ' AND date_depense BETWEEN ? AND ?';
                $params = [$start, $end];
                $label = $this->moisNoms[$safeMonth - 1] . " $currentYear";
            }
        } elseif ($filterType === 'multiple') {
            $conditions = [];
            if (!empty($moisMultiple) && !empty($anneeMultiple)) {
                foreach ($anneeMultiple as $y) {
                    foreach ($moisMultiple as $m) {
                        $sm = max(1, min(12, $m));
                        $start = sprintf('%d-%02d-01', $y, $sm);
                        $end   = date('Y-m-t', strtotime($start));
                        $conditions[] = "(date_depense BETWEEN ? AND ?)";
                        $params[] = $start;
                        $params[] = $end;
                    }
                }
                $label = count($moisMultiple) . ' mois × ' . count($anneeMultiple) . ' année(s)';
            } elseif (!empty($anneeMultiple)) {
                foreach ($anneeMultiple as $y) {
                    $conditions[] = "(date_depense BETWEEN ? AND ?)";
                    $params[] = sprintf('%d-01-01', $y);
                    $params[] = sprintf('%d-12-31', $y);
                }
                $label = 'Années : ' . implode(', ', $anneeMultiple);
            } elseif (!empty($moisMultiple)) {
                foreach ($moisMultiple as $m) {
                    $sm = max(1, min(12, $m));
                    $start = sprintf('%d-%02d-01', $currentYear, $sm);
                    $end   = date('Y-m-t', strtotime($start));
                    $conditions[] = "(date_depense BETWEEN ? AND ?)";
                    $params[] = $start;
                    $params[] = $end;
                }
                $monthNames = array_map(fn($m) => $this->moisNoms[$m - 1], $moisMultiple);
                $label = implode(', ', $monthNames) . " $currentYear";
            } else {
                $label = 'Multi‑sélection (aucun filtre appliqué)';
            }
            if (!empty($conditions)) {
                $sql .= ' AND (' . implode(' OR ', $conditions) . ')';
            }
        } elseif ($filterType === 'range') {
            if ($dateDebut && $dateFin) {
                if ($dateDebut > $dateFin) {
                    [$dateDebut, $dateFin] = [$dateFin, $dateDebut];
                }
                $sql .= ' AND date_depense BETWEEN ? AND ?';
                $params = [$dateDebut, $dateFin];
                $label = "Du $dateDebut au $dateFin";
            } else {
                $label = 'Période non définie';
            }
        }

        return [
            'sql' => $sql,
            'params' => $params,
            'label' => $label,
            'filter_type' => $filterType,
            'mois' => $mois !== '' ? $mois : null,
            'annee' => $annee,
            'currentYear' => $currentYear,
            'moisMultiple' => $moisMultiple,
            'anneeMultiple' => $anneeMultiple,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
        ];
    }

    private function calculerDonnees(array $depenses, array $categories): array
    {
        $totalGeneral = 0.0;
        $totalNb = count($depenses);
        $donneesCategories = [];

        foreach ($categories as $cat) {
            $donneesCategories[$cat['nom_categorie']] = [0.0, 0];
        }
        $donneesCategories['Non catégorisé'] = [0.0, 0];

        foreach ($depenses as $dep) {
            $montant = (float) $dep['montant'];
            $totalGeneral += $montant;
            $nomCat = 'Non catégorisé';
            foreach ($categories as $cat) {
                if ($cat['id_cat'] == $dep['id_categorie']) {
                    $nomCat = $cat['nom_categorie'];
                    break;
                }
            }
            $donneesCategories[$nomCat][0] += $montant;
            $donneesCategories[$nomCat][1]++;
        }

        $donneesCategories = array_filter($donneesCategories, fn($d) => $d[0] > 0);
        uasort($donneesCategories, fn($a, $b) => $b[0] <=> $a[0]);

        return [$totalGeneral, $totalNb, $donneesCategories];
    }
}
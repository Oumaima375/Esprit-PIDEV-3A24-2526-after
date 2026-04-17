<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\DepenseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(
        Request $request,
        CategorieRepository $catRepo,
        DepenseRepository $depRepo
    ): Response {
        $viewMode = $request->query->get('view', 'default'); // 'depenses' ou 'default'
        $categories = $catRepo->findAll();
        $titre = $request->query->get('titre');
        $categorie = $request->query->get('categorie');

        $qb = $depRepo->createQueryBuilder('d');
        if ($titre) {
            $qb->andWhere('d.titre LIKE :titre')->setParameter('titre', '%' . $titre . '%');
        }
        if ($categorie) {
            $qb->andWhere('d.categorie = :cat')->setParameter('cat', $categorie);
        }
        $depenses = $qb->getQuery()->getResult();

        // Mode « dépenses seulement » → retour rapide
        if ($viewMode === 'depenses') {
            return $this->render('admin/dashboard.html.twig', [
                'categories'   => $categories,
                'depenses'     => $depenses,
                'viewMode'     => 'depenses',
                'filtreTitre'  => $titre,
                'filtreCat'    => $categorie,
            ]);
        }

        // Mode normal (dashboard complet)
        $totalMontant = 0;
        $maxDepense   = null;
        $minDepense   = null;

        foreach ($depenses as $dep) {
            $totalMontant += $dep->getMontant();
            if ($maxDepense === null || $dep->getMontant() > $maxDepense->getMontant()) {
                $maxDepense = $dep;
            }
            if ($minDepense === null || $dep->getMontant() < $minDepense->getMontant()) {
                $minDepense = $dep;
            }
        }

        $moyenne = count($depenses) > 0 ? $totalMontant / count($depenses) : 0;

        // Statistiques pour le camembert
        $statsPieMap = [];
        if ($categorie) {
            // Si une catégorie précise est filtrée, on éclate par dépense (titre)
            foreach ($depenses as $dep) {
                $t = $dep->getTitre();
                if (!isset($statsPieMap[$t])) {
                    $statsPieMap[$t] = ['nom' => $t, 'nb' => 0, 'total' => 0, 'iconeUrl' => null];
                }
                $statsPieMap[$t]['nb']++;
                $statsPieMap[$t]['total'] += $dep->getMontant();
            }
        } else {
            // Répartition classique par catégorie
            foreach ($depenses as $dep) {
                if ($dep->getCategorie()) {
                    $cId = $dep->getCategorie()->getId();
                    if (!isset($statsPieMap[$cId])) {
                        $statsPieMap[$cId] = [
                            'nom' => $dep->getCategorie()->getNomCategorie(),
                            'nb' => 0,
                            'total' => 0,
                            'iconeUrl' => $dep->getCategorie()->getIconeUrl()
                        ];
                    }
                    $statsPieMap[$cId]['nb']++;
                    $statsPieMap[$cId]['total'] += $dep->getMontant();
                }
            }
        }

        $statsPie = [];
        foreach ($statsPieMap as $item) {
            if ($item['total'] > 0 || $item['nb'] > 0) {
                $statsPie[] = $item;
            }
        }
        usort($statsPie, fn($a, $b) => $b['total'] <=> $a['total']);

        // Graphique chronologique
        $chronoMap = [];
        foreach ($depenses as $dep) {
            $dateKey = $dep->getDateDepense()->format('d/m/Y');
            $dateSort = $dep->getDateDepense()->format('Ymd');
            if (!isset($chronoMap[$dateSort])) {
                $chronoMap[$dateSort] = ['label' => $dateKey, 'tot' => 0, 'nb' => 0];
            }
            $chronoMap[$dateSort]['tot'] += $dep->getMontant();
            $chronoMap[$dateSort]['nb']++;
        }
        ksort($chronoMap);

        $graphData = [
            'chronoLabels' => array_column($chronoMap, 'label'),
            'chronoTotals' => array_column($chronoMap, 'tot'),
            'chronoAvgs'   => array_map(fn($v) => round($v['tot'] / $v['nb'], 2), $chronoMap),
            'pieLabels'    => array_column($statsPie, 'nom'),
            'pieTotals'    => array_column($statsPie, 'total'),
            'pieCounts'    => array_column($statsPie, 'nb'),
            'isModeDepense'=> !empty($categorie)
        ];

        return $this->render('admin/dashboard.html.twig', [
            'categories'   => $categories,
            'depenses'     => $depenses,
            'totalMontant' => $totalMontant,
            'moyenne'      => $moyenne,
            'maxDepense'   => $maxDepense,
            'minDepense'   => $minDepense,
            'statsCat'     => $statsPie,
            'filtreTitre'  => $titre,
            'filtreCat'    => $categorie,
            'graphData'    => json_encode($graphData),
            'viewMode'     => 'default',
        ]);
    }
}
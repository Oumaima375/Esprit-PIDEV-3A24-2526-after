<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\DepenseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(DepenseRepository $depRepo, CategorieRepository $catRepo): Response
    {
        $now = new \DateTime();
        $debutMois = new \DateTime($now->format('Y-m-01'));
        $finMois   = new \DateTime($now->format('Y-m-t'));

        $depensesThisMois = $depRepo->createQueryBuilder('d')
            ->select('SUM(d.montant)')
            ->where('d.dateDepense >= :debut')
            ->andWhere('d.dateDepense <= :fin')
            ->setParameter('debut', $debutMois)
            ->setParameter('fin', $finMois)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        $allDepenses = $depRepo->findAll();
        $total = array_sum(array_map(fn($d) => $d->getMontant(), $allDepenses));

        return $this->render('home/index.html.twig', [
            'totalDepenses'   => number_format((float)$total, 2),
            'nbDepenses'      => count($allDepenses),
            'nbCategories'    => count($catRepo->findAll()),
            'depensesMois'    => number_format((float)$depensesThisMois, 2),
            'dernierDepenses' => $depRepo->findBy([], ['dateDepense' => 'DESC'], 3),
        ]);
    }
}
<?php

namespace App\Controller;

use App\Repository\DepenseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MapController extends AbstractController
{
    #[Route('/carte-depenses', name: 'carte_depenses')]
    public function index(DepenseRepository $depenseRepo): Response
    {
        $depenses = $depenseRepo->getDepensesWithLocation();
        return $this->render('map/index.html.twig', [
            'depenses' => $depenses,
        ]);
    }
}

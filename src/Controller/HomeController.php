<?php

namespace App\Controller;

use App\Repository\VoyageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(VoyageRepository $voyageRepository): Response
    {
        // Les 3 voyages les plus récents
        $voyages = $voyageRepository->findBy([], ['date_debut' => 'DESC'], 3);
        return $this->render('home/index.html.twig', [
            'voyages' => $voyages,
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }
}
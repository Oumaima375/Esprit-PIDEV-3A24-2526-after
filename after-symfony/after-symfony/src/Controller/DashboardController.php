<?php
 
namespace App\Controller;
 
use App\Repository\ActiviteRepository;
use App\Repository\PlanningRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
 
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        ActiviteRepository $activiteRepository,
        PlanningRepository $planningRepository
    ): Response {
        $activites = $activiteRepository->findAll();
        $plannings = $planningRepository->findAll();
 
        return $this->render('dashboard/index.html.twig', [
            'activites' => $activites,
            'plannings' => $plannings,
        ]);
    }
}
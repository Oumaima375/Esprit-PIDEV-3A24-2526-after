<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Form\ActiviteType;
use App\Repository\ActiviteRepository;
use App\Repository\PlanningRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

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
            'activites'      => $activites,
            'plannings'      => $plannings,
            'totalActivites' => count($activites),
            'totalPlannings' => count($plannings),
        ]);
    }

    // ===================== ACTIVITE ADD =====================
    #[Route('/dashboard/activite/add', name: 'app_dashboard_activite_add')]
    public function activiteAdd(Request $request, EntityManagerInterface $em): Response
    {
        $activite = new Activite();
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($activite);
            $em->flush();
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('dashboard/activite_add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ===================== ACTIVITE EDIT =====================
    #[Route('/dashboard/activite/edit/{id}', name: 'app_dashboard_activite_edit')]
    public function activiteEdit(int $id, Request $request, ActiviteRepository $activiteRepository, EntityManagerInterface $em): Response
    {
        $activite = $activiteRepository->find($id);

        if (!$activite) {
            throw $this->createNotFoundException('Activité introuvable');
        }

        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('dashboard/activite_edit.html.twig', [
            'form'     => $form->createView(),
            'activite' => $activite,
        ]);
    }

    // ===================== ACTIVITE DELETE =====================
    #[Route('/dashboard/activite/delete/{id}', name: 'app_dashboard_activite_delete')]
    public function activiteDelete(int $id, ActiviteRepository $activiteRepository, EntityManagerInterface $em): Response
    {
        $activite = $activiteRepository->find($id);

        if (!$activite) {
            throw $this->createNotFoundException('Activité introuvable');
        }

        $em->remove($activite);
        $em->flush();
        return $this->redirectToRoute('app_dashboard');
    }
}
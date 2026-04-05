<?php

namespace App\Controller;

use App\Entity\Planning;
use App\Form\PlanningType;
use App\Repository\PlanningRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use Symfony\Contracts\HttpClient\HttpClientInterface;


class PlanningController extends AbstractController
{
    #[Route('/planning', name: 'app_planning')]
    public function index(PlanningRepository $planningRepository): Response
    {
        $plannings = $planningRepository->findAll();
        return $this->render('planning/index.html.twig', [
            'plannings' => $plannings,
        ]);
    }

    // ===================== RECHERCHE AJAX =====================
    #[Route('/planning/search', name: 'app_planning_search')]
    public function search(
        Request $request,
        NormalizerInterface $normalizer,
        PlanningRepository $planningRepository
    ): JsonResponse {
        $searchValue = $request->get('searchValue');
        $plannings = $planningRepository->findPlanningByNom($searchValue);
        $jsonContent = $normalizer->normalize($plannings, 'json', ['groups' => 'plannings']);
        return new JsonResponse($jsonContent);
    }

    // ===================== TRI AJAX =====================
    #[Route('/planning/sort', name: 'app_planning_sort')]
    public function sort(
        Request $request,
        NormalizerInterface $normalizer,
        PlanningRepository $planningRepository
    ): JsonResponse {
        $sortBy = $request->get('sortBy', 'nom');
        $plannings = $planningRepository->findPlanningsSorted($sortBy);
        $jsonContent = $normalizer->normalize($plannings, 'json', ['groups' => 'plannings']);
        return new JsonResponse($jsonContent);
    }

    #[Route('/planning/add', name: 'app_planning_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $planning = new Planning();
        $planning->setIdUser(1);
        $planning->setDuree(0);
        $planning->setHeureDebut('00:00');

        $form = $this->createForm(PlanningType::class, $planning);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($planning);
            $em->flush();
            return $this->redirectToRoute('app_planning');
        }

        return $this->render('planning/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/planning/edit/{id}', name: 'app_planning_edit')]
    public function edit(int $id, Request $request, PlanningRepository $planningRepository, EntityManagerInterface $em): Response
    {
        $planning = $planningRepository->find($id);
        $form = $this->createForm(PlanningType::class, $planning);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_planning');
        }

        return $this->render('planning/edit.html.twig', [
            'form' => $form->createView(),
            'planning' => $planning,
        ]);
    }

    #[Route('/planning/delete/{id}', name: 'app_planning_delete')]
    public function delete(int $id, PlanningRepository $planningRepository, EntityManagerInterface $em): Response
    {
        $planning = $planningRepository->find($id);
        $em->remove($planning);
        $em->flush();
        return $this->redirectToRoute('app_planning');
    }
  

#[Route('/pays/liste', name: 'app_pays_liste')]
public function paysListe(HttpClientInterface $client): JsonResponse
{
    $response = $client->request('GET',
        'https://restcountries.com/v3.1/all?fields=name'
    );
    $data = $response->toArray();
    $pays = [];
    foreach ($data as $country) {
        $pays[] = $country['name']['common'];
    }
    sort($pays);
    return $this->json($pays);
}
#[Route('/pays/info/{nom}', name: 'app_pays_info')]
public function paysInfo(string $nom, HttpClientInterface $client): JsonResponse
{
    try {
        $response = $client->request('GET',
            'https://restcountries.com/v3.1/name/' . urlencode($nom) . '?fullText=true'
        );
        $data = $response->toArray();
        $country = $data[0];
        return $this->json([
            'capitale'   => $country['capital'][0] ?? 'N/A',
            'region'     => $country['region'] ?? 'N/A',
            'population' => number_format($country['population'], 0, ',', ' '),
            'drapeau'    => $country['flags']['png'] ?? '',
            'nom'        => $country['name']['common'] ?? $nom,
        ]);
    } catch (\Exception $e) {
        return $this->json(['erreur' => $e->getMessage()], 500);
    }
}
}
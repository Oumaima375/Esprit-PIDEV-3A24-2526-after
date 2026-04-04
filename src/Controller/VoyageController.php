<?php

namespace App\Controller;

use App\Entity\Voyage;
use App\Repository\VoyageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class VoyageController extends AbstractController
{
    #[Route('/voyage', name: 'app_voyage')]
public function index(VoyageRepository $voyageRepository, Request $request): Response
{
    $search = $request->query->get('search');
    $prixMin = $request->query->get('prix_min');
    $prixMax = $request->query->get('prix_max');
    $dateDebut = $request->query->get('date_debut');
    $placesMin = $request->query->get('places_min');

    $voyages = $voyageRepository->findWithFilters($search, $prixMin, $prixMax, $dateDebut, $placesMin);

    return $this->render('voyage/index.html.twig', [
        'voyages' => $voyages,
    ]);
}

    #[Route('/voyage/favoris', name: 'app_voyage_favoris')]
    public function favoris(VoyageRepository $voyageRepository, Request $request): Response
    {
        $favorisIds = $request->getSession()->get('favoris', []);
        $voyages = empty($favorisIds) ? [] : $voyageRepository->findBy(['id' => $favorisIds]);
        return $this->render('voyage/favoris.html.twig', [
            'voyages' => $voyages,
        ]);
    }

    #[Route('/voyage/recommandations', name: 'app_voyage_recommandations')]
public function recommandations(VoyageRepository $voyageRepository, Request $request): Response
{
    $search = $request->query->get('search');
    $prixMin = $request->query->get('prix_min');
    $prixMax = $request->query->get('prix_max');
    $dateDebut = $request->query->get('date_debut');
    $placesMin = $request->query->get('places_min');

    $voyages = $voyageRepository->findWithFilters($search, $prixMin, $prixMax, $dateDebut, $placesMin);

    return $this->render('voyage/recommandations.html.twig', [
        'voyages' => $voyages,
    ]);
}
#[Route('/voyage/toggle-favori/{id}', name: 'app_voyage_toggle_favori')]
public function toggleFavori(int $id, Request $request): Response
{
    $session = $request->getSession();
    $favoris = $session->get('favoris', []);

    if (in_array($id, $favoris)) {
        $favoris = array_filter($favoris, fn($f) => $f !== $id);
    } else {
        $favoris[] = $id;
    }

    $session->set('favoris', array_values($favoris));
    return $this->redirectToRoute('app_voyage');
}

#[Route('/voyage/new', name: 'app_voyage_new')]
public function new(): Response
{
    return $this->render('voyage/new.html.twig');
}
}
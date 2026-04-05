<?php

namespace App\Controller;

use App\Repository\VoyageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\VoyageType;
use Doctrine\ORM\EntityManagerInterface;

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
        $voyages = [];
        foreach ($favorisIds as $id) {
            $voyage = $voyageRepository->findOneBy(['id_voyage' => $id]);
            if ($voyage) $voyages[] = $voyage;
        }
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
public function new(Request $request, EntityManagerInterface $em): Response
{
    $voyage = new \App\Entity\Voyage();
    $form = $this->createForm(VoyageType::class, $voyage);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Récupérer l'image URL manuellement
        $imageUrl = $request->request->all()['voyage_type']['image'] ?? null;
        $imageFile = $form->get('imageFile')->getData();

        if ($imageFile) {
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move(
                $this->getParameter('kernel.project_dir') . '/public/img/',
                $newFilename
            );
            $voyage->setImage('/img/' . $newFilename);
        } elseif ($imageUrl) {
            $voyage->setImage($imageUrl);
        }

        $em->persist($voyage);
        $em->flush();

        $this->addFlash('success', 'Voyage ajouté avec succès !');
        return $this->redirectToRoute('app_voyage');
    }

    return $this->render('voyage/new.html.twig', [
        'form' => $form->createView(),
    ]);
}
}
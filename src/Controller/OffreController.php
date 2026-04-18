<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Entity\Service;
use App\Form\OffreType;
use App\Service\WeatherService;
use App\Service\OffreRecommandationService;
use App\Service\OffreNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/offre')]
class OffreController extends AbstractController
{
    // ─────────────────────────────────────────────────────────────────
    // LIST  (with search, filter, and AI recommendation)
    // ─────────────────────────────────────────────────────────────────
    #[Route('/', name: 'app_offre_index', methods: ['GET'])]
    public function index(
        Request                     $request,
        EntityManagerInterface      $em,
        OffreRecommandationService  $recommandation,
        WeatherService              $weather,
    ): Response {
        $search    = $request->query->get('search', '');
        $serviceId = $request->query->get('service_id', '');
        $mood      = $request->query->get('mood', '');
        $maxPrix   = (float) $request->query->get('max_prix', 0);

        // ── AI recommendation mode ────────────────────────
        $recommendations = [];
        if ($mood) {
            $recommendations = $recommandation->recommend($mood, $maxPrix, 6);
        }

        // ── Standard list ─────────────────────────────────
        $qb = $em->getRepository(Offre::class)->createQueryBuilder('o');

        if ($search) {
            $qb->andWhere('o.titre LIKE :search OR o.destination LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        if ($serviceId) {
            $qb->andWhere('o.id_service = :service')
               ->setParameter('service', $serviceId);
        }

        $offres   = $qb->getQuery()->getResult();
        $services = $em->getRepository(Service::class)->findAll();

        // ── Attach weather to each offer (uses cache) ─────
        $weatherData = [];
        foreach ($offres as $offre) {
            if ($offre->getDestination()) {
                $weatherData[$offre->getId()] = $weather->getWeatherForOffre($offre);
            }
        }

        // ── Count expiring-soon offers for banner ─────────
        $expirantBientot = array_filter(
            $offres,
            fn(Offre $o) => $o->joursRestants() !== null && $o->joursRestants() <= 3
        );

        return $this->render('offre/index.html.twig', [
            'offres'           => $offres,
            'services'         => $services,
            'weatherData'      => $weatherData,
            'recommendations'  => $recommendations,
            'mood'             => $mood,
            'expirantBientot'  => count($expirantBientot),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // SHOW  (with weather + Leaflet map)
    // ─────────────────────────────────────────────────────────────────
    #[Route('/{id}', name: 'app_offre_show', methods: ['GET'])]
    public function show(Offre $offre, WeatherService $weather): Response
    {
        $weatherData = $weather->getWeatherForOffre($offre);

        return $this->render('offre/show.html.twig', [
            'offre'       => $offre,
            'weatherData' => $weatherData,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // NEW
    // ─────────────────────────────────────────────────────────────────
    #[Route('/new', name: 'app_offre_new', methods: ['GET', 'POST'])]
    public function new(
        Request                      $request,
        EntityManagerInterface       $em,
        OffreNotificationService     $notif,
    ): Response {
        $offre = new Offre();
        $form  = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($offre);
            $em->flush();

            // 🔔 Notify all users of new offer
            $notif->triggerNewOffreNotification($offre);

            $this->addFlash('success', '✅ Offre créée avec succès ! Les utilisateurs ont été notifiés.');
            return $this->redirectToRoute('app_offre_index');
        }

        return $this->render('offre/new.html.twig', [
            'offre' => $offre,
            'form'  => $form->createView(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // EDIT  (with price-drop detection)
    // ─────────────────────────────────────────────────────────────────
    #[Route('/{id}/edit', name: 'app_offre_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request                  $request,
        Offre                    $offre,
        EntityManagerInterface   $em,
        OffreNotificationService $notif,
    ): Response {
        $oldPrice = $offre->getPrix();

        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Invalidate weather cache if destination changed
            if ($offre->getDestination()) {
                $offre->setWeatherCache(null);
            }

            $em->flush();

            // 🔔 Notify users of price drop
            $notif->triggerPriceDropNotification($offre, $oldPrice);

            $this->addFlash('success', '✅ Offre modifiée avec succès !');
            return $this->redirectToRoute('app_offre_index');
        }

        return $this->render('offre/edit.html.twig', [
            'offre' => $offre,
            'form'  => $form->createView(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // DELETE
    // ─────────────────────────────────────────────────────────────────
    #[Route('/{id}/delete', name: 'app_offre_delete', methods: ['POST'])]
    public function delete(Request $request, Offre $offre, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $offre->getId_offre(), $request->request->get('_token'))) {
            $em->remove($offre);
            $em->flush();
            $this->addFlash('success', 'Offre supprimée !');
        }
        return $this->redirectToRoute('app_offre_index');
    }

    // ─────────────────────────────────────────────────────────────────
    // AJAX: Toggle Favorite  ❤️
    // ─────────────────────────────────────────────────────────────────
    #[Route('/{id}/favori', name: 'app_offre_favori', methods: ['POST'])]
    public function toggleFavori(Offre $offre, EntityManagerInterface $em): JsonResponse
    {
        // In a real app, you'd store a UserOffre join table.
        // Here we just increment/decrement the global favorisCount.
        $session    = $this->container->get('request_stack')->getSession();
        $favKey     = 'fav_offre_' . $offre->getId();
        $isFav      = $session->get($favKey, false);

        if ($isFav) {
            $offre->setFavorisCount(max(0, $offre->getFavorisCount() - 1));
            $session->set($favKey, false);
            $status = 'removed';
        } else {
            $offre->setFavorisCount($offre->getFavorisCount() + 1);
            $session->set($favKey, true);
            $status = 'added';
        }

        $em->flush();

        return $this->json([
            'status'        => $status,
            'favorisCount'  => $offre->getFavorisCount(),
            'isFav'         => !$isFav,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // AJAX: Refresh Weather
    // ─────────────────────────────────────────────────────────────────
    #[Route('/{id}/weather', name: 'app_offre_weather', methods: ['GET'])]
    public function refreshWeather(Offre $offre, WeatherService $weather): JsonResponse
    {
        // Force refresh
        $offre->setWeatherCache(null);
        $data = $weather->getWeatherForOffre($offre);

        return $this->json($data ?? ['error' => 'Météo indisponible']);
    }

    // ─────────────────────────────────────────────────────────────────
    // EXPORT PDF
    // ─────────────────────────────────────────────────────────────────
    #[Route('/export/pdf', name: 'app_offre_export_pdf', methods: ['GET'])]
    public function exportPdf(EntityManagerInterface $em): Response
    {
        $offres = $em->getRepository(Offre::class)->findAll();

        $html  = '<html><body style="font-family:Arial,sans-serif;">';
        $html .= '<h1 style="color:#1a3a6e;text-align:center;">Liste des Offres — After Travel</h1>';
        $html .= '<table border="1" width="100%" cellpadding="8" style="border-collapse:collapse;">';
        $html .= '<thead><tr style="background:#1a3a6e;color:white;">
                    <th>Titre</th>
                    <th>Prix (€)</th>
                    <th>Durée (j)</th>
                    <th>Destination</th>
                    <th>Disponibilité</th>
                    <th>Service</th>
                  </tr></thead><tbody>';

        foreach ($offres as $offre) {
            $dispo = $offre->isDisponible() ? '✓ Disponible' : '✗ Indisponible';
            $dates = '';
            if ($offre->getDateDebut()) {
                $dates = $offre->getDateDebut()->format('d/m/Y');
            }
            if ($offre->getDateFin()) {
                $dates .= ' → ' . $offre->getDateFin()->format('d/m/Y');
            }
            $html .= '<tr>
                <td>' . $offre->getTitre() . '</td>
                <td>' . $offre->getPrix() . '</td>
                <td>' . $offre->getDuree() . '</td>
                <td>' . ($offre->getDestination() ?? '—') . '</td>
                <td>' . $dispo . ($dates ? "<br><small>$dates</small>" : '') . '</td>
                <td>' . $offre->getIdService()->getNomService() . '</td>
            </tr>';
        }

        $html .= '</tbody></table></body></html>';

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="offres.pdf"',
            ]
        );
    }
}
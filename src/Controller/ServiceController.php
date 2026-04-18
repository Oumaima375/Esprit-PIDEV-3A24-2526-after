<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\Offre;
use App\Form\ServiceType;
use App\Service\OffreNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/service')]
class ServiceController extends AbstractController
{
    // ─────────────────────────────────────────────────────────────────
    // LIST
    // ─────────────────────────────────────────────────────────────────
    #[Route('/', name: 'app_service_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $search = $request->query->get('search', '');

        $qb = $em->getRepository(Service::class)->createQueryBuilder('s');

        if ($search) {
            $qb->where('s.nom_service LIKE :search OR s.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $services = $qb->getQuery()->getResult();

        // Count total offers for quick stats banner
        $totalOffres = $em->getRepository(Offre::class)->count([]);

        return $this->render('service/index.html.twig', [
            'services'    => $services,
            'totalOffres' => $totalOffres,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // NEW
    // ─────────────────────────────────────────────────────────────────
    #[Route('/new', name: 'app_service_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $service = new Service();
        $form    = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($service);
            $em->flush();
            $this->addFlash('success', '✅ Service créé avec succès !');
            return $this->redirectToRoute('app_service_index');
        }

        return $this->render('service/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // STATISTICS (with Google Charts data)
    // ─────────────────────────────────────────────────────────────────
    #[Route('/statistiques', name: 'app_service_stats', methods: ['GET'])]
    public function statistiques(EntityManagerInterface $em): Response
    {
        $nbServices = $em->getRepository(Service::class)->count([]);
        $nbOffres   = $em->getRepository(Offre::class)->count([]);

        $prixMoyen = (float) $em->createQuery(
            'SELECT AVG(o.prix) FROM App\Entity\Offre o'
        )->getSingleScalarResult();

        $prixMax = (float) $em->createQuery(
            'SELECT MAX(o.prix) FROM App\Entity\Offre o'
        )->getSingleScalarResult();

        $prixMin = (float) $em->createQuery(
            'SELECT MIN(o.prix) FROM App\Entity\Offre o'
        )->getSingleScalarResult();

        // Offers per service
        $offresParService = $em->createQuery(
            'SELECT s.nom_service AS titre, COUNT(o.id_offre) as nbOffres,
                    AVG(o.prix) as prixMoyen, MAX(o.prix) as prixMax
             FROM App\Entity\Service s
             LEFT JOIN s.offres o
             GROUP BY s.id_service'
        )->getResult();

        // Price distribution buckets (for histogram)
        $allPrices = $em->createQuery('SELECT o.prix FROM App\Entity\Offre o')->getScalarResult();
        $priceBuckets = $this->buildPriceBuckets(array_column($allPrices, 'prix'));

        // Availability stats
        $totalDisponibles = 0;
        $totalExpires     = 0;
        foreach ($em->getRepository(Offre::class)->findAll() as $o) {
            $o->isDisponible() ? $totalDisponibles++ : $totalExpires++;
        }

        // Favorites leaderboard (top 5)
        $topFavoris = $em->createQuery(
            'SELECT o FROM App\Entity\Offre o ORDER BY o.favorisCount DESC'
        )->setMaxResults(5)->getResult();

        return $this->render('service/statistiques.html.twig', [
            'nbServices'        => $nbServices,
            'nbOffres'          => $nbOffres,
            'prixMoyen'         => round($prixMoyen, 2),
            'prixMax'           => $prixMax,
            'prixMin'           => $prixMin,
            'offresParService'  => $offresParService,
            'priceBuckets'      => $priceBuckets,
            'totalDisponibles'  => $totalDisponibles,
            'totalExpires'      => $totalExpires,
            'topFavoris'        => $topFavoris,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────────
    #[Route('/{id}', name: 'app_service_show', methods: ['GET'])]
    public function show(Service $service): Response
    {
        return $this->render('service/show.html.twig', [
            'service' => $service,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────────────────────────
    #[Route('/{id}/edit', name: 'app_service_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Service $service, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', '✅ Service modifié avec succès !');
            return $this->redirectToRoute('app_service_index');
        }

        return $this->render('service/edit.html.twig', [
            'service' => $service,
            'form'    => $form->createView(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // DELETE
    // ─────────────────────────────────────────────────────────────────
    #[Route('/{id}/delete', name: 'app_service_delete', methods: ['POST'])]
    public function delete(Request $request, Service $service, EntityManagerInterface $em): Response
    {
        if ($service->getOffres()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer ce service car il contient des offres !');
            return $this->redirectToRoute('app_service_index');
        }

        if ($this->isCsrfTokenValid('delete' . $service->getId_service(), $request->request->get('_token'))) {
            $em->remove($service);
            $em->flush();
            $this->addFlash('success', 'Service supprimé !');
        }

        return $this->redirectToRoute('app_service_index');
    }

    // ─────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────
    private function buildPriceBuckets(array $prices): array
    {
        if (empty($prices)) return [];

        $min    = min($prices);
        $max    = max($prices);
        $range  = $max - $min;
        $step   = $range > 0 ? ceil($range / 5) : 100;
        $step   = max($step, 50);
        $bucket = [];

        for ($i = $min; $i <= $max; $i += $step) {
            $label = (int)$i . '–' . (int)($i + $step - 1) . ' €';
            $bucket[$label] = 0;
        }

        foreach ($prices as $p) {
            foreach (array_keys($bucket) as $label) {
                [$lo, $hi] = sscanf($label, '%d–%d €');
                if ($p >= $lo && $p <= $hi) {
                    $bucket[$label]++;
                    break;
                }
            }
        }

        return $bucket;
    }
}
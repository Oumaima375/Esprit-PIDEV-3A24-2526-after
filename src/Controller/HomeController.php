<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use App\Repository\VoyageRepository;
use App\Repository\DestinationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_root')]
    public function root(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->redirectToRoute('app_login');
    }

    #[Route('/home', name: 'app_home')]
    public function index(VoyageRepository $voyageRepository): Response
    {
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

    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function dashboard(
        UsersRepository $usersRepository,
        VoyageRepository $voyageRepository,
        DestinationRepository $destinationRepository,
    ): Response {
        // ── ADMIN ──────────────────────────────────────────────────────────────
        if ($this->isGranted('ROLE_ADMIN')) {

            // Users stats (for users section embedded in dashboard)
            $users        = $usersRepository->findAll();
            $totalUsers   = count($users);
            $totalAdmins  = 0;
            $totalVoyageurs = 0;
            foreach ($users as $u) {
                if (in_array('ROLE_ADMIN', $u->getRoles(), true)) {
                    ++$totalAdmins;
                } else {
                    ++$totalVoyageurs;
                }
            }

            // Voyage / destination stats (for the admin dashboard cards)
            $allVoyages   = $voyageRepository->findAll();
            $totalRevenue = 0;
            $totalPlaces  = 0;
            foreach ($allVoyages as $v) {
                $totalPlaces  += $v->getNbPlaces();
                $totalRevenue += $v->getPrix();
            }
            $prixMoyen = count($allVoyages) > 0 ? round($totalRevenue / count($allVoyages)) : 0;

            $destinations = $destinationRepository->findAll();
            $statsDestinations = [];
            foreach ($destinations as $d) {
                $statsDestinations[] = [
                    'pays'  => $d->getPays(),
                    'count' => count($voyageRepository->findBy(['id_destination' => $d])),
                ];
            }
            usort($statsDestinations, fn ($a, $b) => $b['count'] - $a['count']);

            return $this->render('dashboard/dashboard.html.twig', [
                // Users
                'users'          => $users,
                'totalUsers'     => $totalUsers,
                'totalVoyageurs' => $totalVoyageurs,
                'totalAdmins'    => $totalAdmins,
                // Voyages
                'totalVoyages'      => count($allVoyages),
                'totalDestinations' => count($destinations),
                'prixMoyen'         => $prixMoyen,
                'recentVoyages'     => $voyageRepository->findBy([], ['id_voyage' => 'DESC'], 5),
                'statsDestinations' => array_slice($statsDestinations, 0, 5),
                'totalPlaces'       => $totalPlaces,
            ]);
        }

        // ── VOYAGEUR ───────────────────────────────────────────────────────────
        $user = $this->getUser();
        if (!$user instanceof Users) {
            throw $this->createAccessDeniedException();
        }

        $reservations = $user->getReservations();
        $documents    = $user->getDocuments();

        // Voyageur sees the public home page (with recent voyages + profile info)
        $voyages = $voyageRepository->findBy([], ['date_debut' => 'DESC'], 3);

        return $this->render('home/index.html.twig', [
            'voyages'              => $voyages,
            'reservations_count'   => $reservations->count(),
            'documents_count'      => $documents->count(),
            'recent_reservations'  => $reservations->slice(0, 5),
        ]);
    }
}
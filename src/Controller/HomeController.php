<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
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

    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function dashboard(UsersRepository $usersRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $users = $usersRepository->findAll();
            $totalUsers = count($users);
            $totalAdmins = 0;
            $totalVoyageurs = 0;
            foreach ($users as $user) {
                if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                    ++$totalAdmins;
                } else {
                    ++$totalVoyageurs;
                }
            }

            return $this->render('index.html.twig', [
                'users' => $users,
                'totalUsers' => $totalUsers,
                'totalVoyageurs' => $totalVoyageurs,
                'totalAdmins' => $totalAdmins,
            ]);
        }

        $user = $this->getUser();
        if (!$user instanceof Users) {
            throw $this->createAccessDeniedException();
        }

        $reservations = $user->getReservations();
        $documents = $user->getDocuments();

        return $this->render('dashboard/voyageur.html.twig', [
            'reservations_count' => $reservations->count(),
            'documents_count' => $documents->count(),
            'recent_reservations' => $reservations->slice(0, 5),
        ]);
    }
}

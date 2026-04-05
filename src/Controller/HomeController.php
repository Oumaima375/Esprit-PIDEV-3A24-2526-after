<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UsersRepository;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
public function index(UsersRepository $usersRepository): Response
    {
        $users = $usersRepository->findAll();
        
        $totalUsers = count($users);
        $totalAdmins = 0;
        $totalVoyageurs = 0;
        foreach ($users as $user) {
            $isAdmin = in_array('ROLE_ADMIN', $user->getRoles() ?? []);
            if ($isAdmin) {
                $totalAdmins++;
            } else {
                $totalVoyageurs++;
            }
        }

        return $this->render('index.html.twig', [
            'users' => $users,
            'totalUsers' => $totalUsers,
            'totalVoyageurs' => $totalVoyageurs,
            'totalAdmins' => $totalAdmins,
        ]);
    }
}
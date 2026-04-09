<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'app_profile', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        SluggerInterface $slugger,
        UsersRepository $usersRepository,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof Users) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            $tokenId = 'profile_edit_' . $user->getId();
            if (!$this->isCsrfTokenValid($tokenId, $request->request->get('_csrf_token'))) {
                $this->addFlash('error', 'Token de sécurité invalide.');

                return $this->redirectToRoute('app_profile');
            }

            $nom = trim((string) $request->request->get('nom'));
            $prenom = trim((string) $request->request->get('prenom'));
            $email = trim((string) $request->request->get('email'));
            $telephone = trim((string) $request->request->get('telephone'));
            $password = (string) $request->request->get('password');

            $errors = [];
            if ($nom === '') {
                $errors['nom'] = 'Le nom est requis.';
            }
            if ($prenom === '') {
                $errors['prenom'] = 'Le prénom est requis.';
            }
            if ($email === '') {
                $errors['email'] = 'L\'email est requis.';
            } elseif (!str_contains($email, '@')) {
                $errors['email'] = 'L\'email doit contenir le symbole @ (exemple : nom@domaine.com).';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Le type ou le format de l\'email n\'est pas valide.';
            } elseif ($usersRepository->isEmailTaken($email, $user->getId())) {
                $errors['email'] = 'Un utilisateur existe déjà avec cette adresse email.';
            }
            if ($telephone !== '' && mb_strlen($telephone) > 20) {
                $errors['telephone'] = 'Le téléphone ne doit pas dépasser 20 caractères.';
            }
            if ($telephone !== '' && !preg_match('/^[0-9+\s().-]{8,}$/', $telephone)) {
                $errors['telephone'] = 'Le format du téléphone n\'est pas valide.';
            }
            if ($password !== '' && strlen($password) < 6) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères.';
            }

            $photoFile = $request->files->get('photo_profil');
            if ($photoFile && $photoFile->isValid()) {
                $mime = $photoFile->getMimeType();
                $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                if (!in_array($mime, $allowed, true)) {
                    $errors['photo_profil'] = 'Formats acceptés : JPG, PNG, WEBP, GIF.';
                }
            }

            if ($errors !== []) {
                foreach ($errors as $msg) {
                    $this->addFlash('error', $msg);
                }

                return $this->redirectToRoute('app_profile');
            }

            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
            $user->setTelephone($telephone !== '' ? $telephone : '');

            if ($password !== '') {
                $user->setPassword($hasher->hashPassword($user, $password));
            }

            if ($photoFile && $photoFile->isValid()) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();
                try {
                    $photoFile->move($this->getParameter('photos_directory'), $newFilename);
                    $user->setPhotoProfil($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo.');

                    return $this->redirectToRoute('app_profile');
                }
            }

            $em->flush();
            $this->addFlash('success', 'Profil mis à jour.');

            return $this->redirectToRoute('app_profile');
        }

        $reservations = $user->getReservations();
        $documents = $user->getDocuments();

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'reservations_count' => $reservations->count(),
            'documents_count' => $documents->count(),
            'recent_reservations' => $reservations->slice(0, 8),
            'recent_documents' => $documents->slice(0, 5),
        ]);
    }
}

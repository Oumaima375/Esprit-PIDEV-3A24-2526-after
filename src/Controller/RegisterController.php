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
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        SluggerInterface $slugger,
        UsersRepository $usersRepository,
    ): Response {
        if (!$this->isCsrfTokenValid('register', $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Session expirée. Veuillez réessayer.');

            return $this->redirectToRoute('app_login');
        }

        $nom = trim((string) $request->request->get('register_nom'));
        $prenom = trim((string) $request->request->get('register_prenom'));
        $email = trim((string) $request->request->get('register_email'));
        $password = (string) $request->request->get('register_password');
        $telephone = trim((string) $request->request->get('register_telephone'));

        $errors = [];
        if ($nom === '') {
            $errors[] = 'Le nom est requis.';
        }
        if ($prenom === '') {
            $errors[] = 'Le prénom est requis.';
        }
        if ($email === '') {
            $errors[] = 'L\'email est requis.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'email n\'est pas valide.';
        }
        if ($password === '' || strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }
        if ($telephone !== '' && !preg_match('/^[0-9+\s().-]{8,}$/', $telephone)) {
            $errors[] = 'Le format du téléphone n\'est pas valide.';
        }
        if ($errors === [] && $usersRepository->isEmailTaken($email)) {
            $errors[] = 'Un utilisateur existe déjà avec cette adresse email.';
        }

        $photoFile = $request->files->get('register_photo');
        if ($photoFile && $photoFile->isValid()) {
            $mime = $photoFile->getMimeType();
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!in_array($mime, $allowed, true)) {
                $errors[] = 'Photo : formats JPG, PNG, WEBP ou GIF uniquement.';
            }
        }

        if ($errors !== []) {
            foreach ($errors as $msg) {
                $this->addFlash('error', $msg);
            }

            return $this->redirectToRoute('app_login');
        }

        $user = new Users();
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setEmail($email);
        $user->setTelephone($telephone !== '' ? $telephone : '');
        $user->setTypeUtilisateur('VOYAGEUR');
        $user->setPassword($hasher->hashPassword($user, $password));

        if ($photoFile && $photoFile->isValid()) {
            $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();
            try {
                $photoFile->move($this->getParameter('photos_directory'), $newFilename);
                $user->setPhotoProfil($newFilename);
            } catch (FileException $e) {
                $user->setPhotoProfil('default.png');
            }
        } else {
            $user->setPhotoProfil('default.png');
        }

        $user->setVerificationToken(bin2hex(random_bytes(32)));
        $user->setVerificationExpiry(new \DateTime('+1 year'));
        $user->setIsVerified(true);

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Compte créé ! Vous pouvez maintenant vous connecter.');

        return $this->redirectToRoute('app_login');
    }
}

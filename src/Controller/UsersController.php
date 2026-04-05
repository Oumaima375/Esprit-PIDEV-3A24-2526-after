<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/user')]
class UsersController extends AbstractController
{
    #[Route('/', name: 'user_index', methods: ['GET'])]
    public function index(UsersRepository $repo): Response
    {
        $users = $repo->findAll();

        return $this->render('index.html.twig', [  
            'users' => $users,
            'totalUsers' => count($users),
            'totalVoyageurs' => $repo->countByRole('VOYAGEUR') ?? 0,
            'totalAdmins' => $repo->countByRole('ADMIN') ?? 0,
        ]);
    }

    // ====================== CRÉATION VIA MODAL (AJAX) ======================
    #[Route('/new', name: 'user_new', methods: ['POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        SluggerInterface $slugger,
        UsersRepository $usersRepository,
    ): Response {
        $user = new Users();

        if (!$this->isCsrfTokenValid('user_new', $request->request->get('_csrf_token'))) {
            return $this->json(['success' => false, 'message' => 'Token invalide'], 403);
        }

        $errors = $this->validateUserInput($request, false);
        if ($errors === []) {
            $email = trim((string) $request->request->get('email'));
            if ($usersRepository->isEmailTaken($email)) {
                $errors['email'] = 'Un utilisateur existe déjà avec cette adresse email.';
            }
        }
        if ($errors !== []) {
            return $this->json([
                'success' => false,
                'message' => 'Veuillez corriger les erreurs du formulaire.',
                'errors' => $errors,
            ], 422);
        }

        $user->setNom($request->request->get('nom'));
        $user->setPrenom($request->request->get('prenom'));
        $user->setEmail($request->request->get('email'));
        $user->setTelephone($request->request->get('telephone'));
        $user->setTypeUtilisateur(strtoupper($request->request->get('type_utilisateur') ?? 'VOYAGEUR'));

        $plainPassword = $request->request->get('password');
        if (!empty($plainPassword)) {
            $user->setPassword($hasher->hashPassword($user, $plainPassword));
        }

        $photoFile = $request->files->get('photo_profil');
        if ($photoFile) {
            $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

            try {
                $photoFile->move($this->getParameter('photos_directory'), $newFilename);
                $user->setPhotoProfil($newFilename);
            } catch (FileException $e) {
                return $this->json(['success' => false, 'message' => 'Erreur photo'], 500);
            }
        } else {
            $user->setPhotoProfil('default.png');
        }

        $user->setVerificationToken(bin2hex(random_bytes(32)));
        $user->setVerificationExpiry(new \DateTime('+24 hours'));
        $user->setIsVerified(false);

        $em->persist($user);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès !',
        ]);
    }

    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    public function show(Users $user): Response
    {
        return $this->render('users/show.html.twig', ['user' => $user]);
    }

    #[Route('/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Users $user,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        SluggerInterface $slugger,
        UsersRepository $usersRepository,
    ): Response {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('user_edit_' . $user->getId(), $request->request->get('_csrf_token'))) {
                if ($this->wantsJson($request)) {
                    return $this->json(['success' => false, 'message' => 'Token de sécurité invalide.'], 403);
                }
                $this->addFlash('error', 'Token de sécurité invalide.');

                return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
            }

            $errors = $this->validateUserInput($request, true);
            if ($errors === []) {
                $email = trim((string) $request->request->get('email'));
                if ($usersRepository->isEmailTaken($email, $user->getId())) {
                    $errors['email'] = 'Un utilisateur existe déjà avec cette adresse email.';
                }
            }
            if ($errors !== []) {
                if ($this->wantsJson($request)) {
                    return $this->json([
                        'success' => false,
                        'message' => 'Veuillez corriger les erreurs du formulaire.',
                        'errors' => $errors,
                    ], 422);
                }
                foreach ($errors as $message) {
                    $this->addFlash('error', $message);
                }

                return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
            }

            $user->setNom($request->request->get('nom'));
            $user->setPrenom($request->request->get('prenom'));
            $user->setEmail($request->request->get('email'));
            $user->setTelephone($request->request->get('telephone'));
            $user->setTypeUtilisateur(strtoupper($request->request->get('type_utilisateur') ?? 'VOYAGEUR'));

            $plainPassword = $request->request->get('password');
            if (!empty($plainPassword)) {
                $user->setPassword($hasher->hashPassword($user, $plainPassword));
            }

            $photoFile = $request->files->get('photo_profil');
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move($this->getParameter('photos_directory'), $newFilename);
                    $user->setPhotoProfil($newFilename);
                } catch (FileException $e) {
                    if ($this->wantsJson($request)) {
                        return $this->json(['success' => false, 'message' => 'Erreur lors du téléchargement de la photo.'], 500);
                    }
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo.');

                    return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
                }
            }

            $em->flush();
            if ($this->wantsJson($request)) {
                return $this->json([
                    'success' => true,
                    'message' => 'Utilisateur modifié avec succès !',
                ]);
            }
            $this->addFlash('success', 'Utilisateur modifié avec succès !');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('users/edit.html.twig', ['user' => $user]);
    }

    #[Route('/{id}/edit-token', name: 'user_edit_token', methods: ['GET'])]
    public function getEditToken(Users $user, CsrfTokenManagerInterface $csrf): Response
{
    $token = $csrf->getToken('user_edit_' . $user->getId());
    return $this->json(['token' => $token->getValue()]);
}

    #[Route('/{id}/delete', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, Users $user, EntityManagerInterface $em): Response
    {
        $tokenId = 'delete' . $user->getId();
        if ($this->isCsrfTokenValid($tokenId, $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token de sécurité invalide.');
        }

        return $this->redirectToRoute('app_home');
    }

    private function wantsJson(Request $request): bool
    {
        if ($request->isXmlHttpRequest()) {
            return true;
        }
        $accept = $request->headers->get('Accept') ?? '';

        return str_contains($accept, 'application/json');
    }

    /**
     * @return array<string, string> field name => message
     */
    private function validateUserInput(Request $request, bool $isEdit): array
    {
        $errors = [];
        $nom = trim((string) $request->request->get('nom'));
        $prenom = trim((string) $request->request->get('prenom'));
        $email = trim((string) $request->request->get('email'));
        $telephone = trim((string) $request->request->get('telephone'));
        $password = (string) $request->request->get('password');
        $type = strtolower(trim((string) $request->request->get('type_utilisateur', 'voyageur')));

        if ($nom === '') {
            $errors['nom'] = 'Le nom est requis.';
        } elseif (mb_strlen($nom) > 100) {
            $errors['nom'] = 'Le nom ne doit pas dépasser 100 caractères.';
        }
        if ($prenom === '') {
            $errors['prenom'] = 'Le prénom est requis.';
        } elseif (mb_strlen($prenom) > 100) {
            $errors['prenom'] = 'Le prénom ne doit pas dépasser 100 caractères.';
        }
        $emailError = $this->validateEmailValue($email);
        if ($emailError !== null) {
            $errors['email'] = $emailError;
        }
        if ($telephone !== '' && mb_strlen($telephone) > 20) {
            $errors['telephone'] = 'Le téléphone ne doit pas dépasser 20 caractères.';
        }
        if ($telephone !== '' && !preg_match('/^[0-9+\s().-]{8,}$/', $telephone)) {
            $errors['telephone'] = 'Le format du téléphone n\'est pas valide.';
        }
        if (!in_array($type, ['admin', 'voyageur'], true)) {
            $errors['type_utilisateur'] = 'Type d\'utilisateur invalide.';
        }

        if ($isEdit) {
            if ($password !== '' && strlen($password) < 6) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères.';
            }
        } else {
            if ($password === '') {
                $errors['password'] = 'Le mot de passe est requis.';
            } elseif (strlen($password) < 6) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères.';
            }
        }

        $photoFile = $request->files->get('photo_profil');
        if ($photoFile && $photoFile->isValid()) {
            $mime = $photoFile->getMimeType();
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!in_array($mime, $allowed, true)) {
                $errors['photo_profil'] = 'Formats acceptés : JPG, PNG, WEBP, GIF.';
            }
        }

        return $errors;
    }

    /**
     * @return string|null error message, or null if the value is a valid non-empty email
     */
    private function validateEmailValue(string $email): ?string
    {
        if ($email === '') {
            return 'L\'email est requis.';
        }
        if (!str_contains($email, '@')) {
            return 'L\'email doit contenir le symbole @ (exemple : nom@domaine.com).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Le type ou le format de l\'email n\'est pas valide : utilisez une adresse avec un domaine correct (ex. contact@societe.fr).';
        }

        return null;
    }
}
<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use App\Service\AuthEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthEmailController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password_request', methods: ['GET', 'POST'])]
    public function forgotPasswordRequest(
        Request $request,
        UsersRepository $usersRepository,
        EntityManagerInterface $em,
        AuthEmailService $authEmailService,
    ): Response {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('forgot_password', $request->request->getString('_csrf_token'))) {
                $this->addFlash('error', 'Session expirée. Réessayez.');

                return $this->redirectToRoute('app_forgot_password_request');
            }

            $email = trim($request->request->getString('email'));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $user = $usersRepository->findOneBy(['email' => $email]);
                if ($user instanceof Users) {
                    $user->setPasswordResetToken(bin2hex(random_bytes(32)));
                    $user->setPasswordResetExpiresAt(new \DateTime('+1 hour'));
                    $em->flush();

                    $url = $this->generateUrl('app_reset_password', ['token' => $user->getPasswordResetToken()], UrlGeneratorInterface::ABSOLUTE_URL);
                    $authEmailService->sendPasswordReset($user, $url);
                }
            }

            $this->addFlash('success', 'Si cette adresse correspond à un compte, vous recevrez un email avec les instructions pour choisir un nouveau mot de passe.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password', requirements: ['token' => '[a-fA-F0-9]{64}'], methods: ['GET', 'POST'])]
    public function resetPassword(
        string $token,
        Request $request,
        UsersRepository $usersRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
    ): Response {
        $user = $usersRepository->findByPasswordResetToken($token);
        if (!$user instanceof Users) {
            $this->addFlash('error', 'Ce lien de réinitialisation n’est pas valide.');

            return $this->redirectToRoute('app_login');
        }

        $expires = $user->getPasswordResetExpiresAt();
        if ($expires === null || $expires < new \DateTimeImmutable()) {
            $this->addFlash('error', 'Ce lien a expiré. Demandez une nouvelle réinitialisation.');
            $user->setPasswordResetToken(null);
            $user->setPasswordResetExpiresAt(null);
            $em->flush();

            return $this->redirectToRoute('app_login');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('reset_password', $request->request->getString('_csrf_token'))) {
                $this->addFlash('error', 'Session expirée. Réessayez.');

                return $this->redirectToRoute('app_reset_password', ['token' => $token]);
            }

            $plain = $request->request->getString('password');
            $plain2 = $request->request->getString('password_confirm');
            if (strlen($plain) < 8) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 8 caractères.');

                return $this->redirectToRoute('app_reset_password', ['token' => $token]);
            }
            if ($plain !== $plain2) {
                $this->addFlash('error', 'Les deux mots de passe ne correspondent pas.');

                return $this->redirectToRoute('app_reset_password', ['token' => $token]);
            }

            $user->setPassword($hasher->hashPassword($user, $plain));
            $user->setPasswordResetToken(null);
            $user->setPasswordResetExpiresAt(null);
            $em->flush();

            $this->addFlash('success', 'Mot de passe mis à jour. Vous pouvez vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'token' => $token,
        ]);
    }
}

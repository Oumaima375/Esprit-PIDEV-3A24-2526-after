<?php

namespace App\Controller;

use App\Repository\PaiementRepository;
use App\Repository\ReservationRepository;
use App\Repository\VoyageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

final class DashboardController extends AbstractController
{
    #[Route('/mes-reservations', name: 'app_dashboard', methods: ['GET'])]
    public function index(
        Request $request,
        ReservationRepository $reservationRepository,
        PaiementRepository $paiementRepository,
        VoyageRepository $voyageRepository,
    ): Response {
        $reservations = [];
        $payments     = [];
        $voyages      = [];
        $dbError      = null;

        $reservationStats = [
            ['value' => '0', 'label' => 'Total Reservations', 'variant' => 'primary'],
            ['value' => '0', 'label' => 'Confirmees',         'variant' => 'success'],
            ['value' => '0', 'label' => 'En Attente',         'variant' => 'warning'],
        ];

        $paymentStats = [
            ['value' => '0',        'label' => 'Nombre de paiements',  'icon' => 'wallet'],
            ['value' => '0,00 TND', 'label' => 'Montant Total Verse',  'icon' => 'badge-dollar-sign'],
        ];

        try {
            $reservations = $reservationRepository->findAll();
            $payments     = $paiementRepository->findAll();
            $voyages      = $voyageRepository->findBy([], ['titre' => 'ASC']);

            $reservationStats = [
                ['value' => (string) $reservationRepository->countAll(),                  'label' => 'Total Reservations', 'variant' => 'primary'],
                ['value' => (string) $reservationRepository->countByStatus('Confirmée'),  'label' => 'Confirmees',         'variant' => 'success'],
                ['value' => (string) $reservationRepository->countByStatus('En attente'), 'label' => 'En Attente',         'variant' => 'warning'],
            ];

            $paymentStats = [
                ['value' => (string) $paiementRepository->countAll(),                                              'label' => 'Nombre de paiements', 'icon' => 'wallet'],
                ['value' => number_format($paiementRepository->sumAllAmounts(), 2, ',', ' ') . ' TND', 'label' => 'Montant Total Verse',  'icon' => 'badge-dollar-sign'],
            ];
        } catch (Throwable $throwable) {
            $dbError = 'Connexion a la base impossible. Verifiez les parametres DB_* dans le fichier .env et votre serveur MySQL.';
            $this->addFlash('error', 'Connexion a la base impossible.');
        }

        return $this->render('dashboard/index.html.twig', [
            'activeView'       => $request->query->get('view', 'reservations'),
            'openModal'        => $request->query->get('modal'),
            'reservationStats' => $reservationStats,
            'paymentStats'     => $paymentStats,
            'reservations'     => $reservations,
            'payments'         => $payments,
            'voyages'          => $voyages,
            'dbError'          => $dbError,
        ]);
    }

    #[Route('/reservation/create', name: 'app_reservation_create', methods: ['POST'])]
    public function createReservation(
        Request $request,
        ReservationRepository $reservationRepository,
    ): RedirectResponse {
        $reservationRepository->create(
            (int)   $request->request->get('voyage_id', 0),
            (int)   $request->request->get('utilisateur_id', 0),
            $this->nullableString($request->request->get('type')),
            $this->nullableString($request->request->get('lieu')),
            $this->nullableString($request->request->get('description')),
            (string) $request->request->get('date_reservation', ''),
            (string) $request->request->get('statut', 'En attente'),
            (int)   $request->request->get('nb_personnes', 0),
            (float) $request->request->get('prix_total', 0),
        );

        $this->addFlash('success', 'Reservation ajoutee avec succes.');

        return $this->redirectToRoute('app_dashboard', ['view' => 'reservations']);
    }

    #[Route('/payment/create', name: 'app_payment_create', methods: ['POST'])]
    public function createPayment(
        Request $request,
        PaiementRepository $paiementRepository,
    ): RedirectResponse {
        $paiementRepository->create(
            (string) $request->request->get('reference', ''),
            (float)  $request->request->get('montant', 0),
            (string) $request->request->get('devise', 'TND'),
            (string) $request->request->get('methode', 'Carte'),
            (string) $request->request->get('statut', 'En attente'),
            (string) $request->request->get('date_paiement', ''),
            (int)    $request->request->get('id_reservation', 0),
        );

        $this->addFlash('success', 'Paiement ajoute avec succes.');

        return $this->redirectToRoute('app_dashboard', ['view' => 'payments']);
    }

    #[Route('/payments/{id}/receipt', name: 'app_payment_receipt', methods: ['GET'])]
    public function paymentReceipt(int $id, PaiementRepository $paiementRepository, ReservationRepository $reservationRepository): Response
    {
        $payment = $paiementRepository->findById($id);
        if (!$payment) {
            throw $this->createNotFoundException('Paiement introuvable.');
        }

        $reservation      = $reservationRepository->findById($payment->getReservationId());
        $verificationCode = sprintf('AFTER-%s-%d', strtoupper($payment->getReference()), $payment->getId());
        $qrPayload        = implode("\n", [
            'After Travel Receipt',
            'Code: '           . $verificationCode,
            'Reference: '      . $payment->getReference(),
            'Montant: '        . $payment->getAmount(),
            'Methode: '        . $payment->getMethode(),
            'Statut: '         . $payment->getStatus(),
            'Reservation: #'   . $payment->getReservationId(),
            'Lieu: '           . ($reservation?->getLieu() ?? $payment->getReservationLieu() ?? 'Non renseigne'),
            'Date Paiement: '  . ($payment->getDatePaiement() ?? 'Non renseignee'),
        ]);

        return $this->render('dashboard/payment_receipt.html.twig', [
            'payment'          => $payment,
            'reservation'      => $reservation,
            'verificationCode' => $verificationCode,
            'qrPayload'        => $qrPayload,
        ]);
    }

    #[Route('/dashboard/reservations/{id}/delete', name: 'app_dashboard_reservation_delete', methods: ['POST'])]
    public function deleteReservation(int $id, ReservationRepository $reservationRepository): RedirectResponse
    {
        $reservationRepository->delete($id);
        $this->addFlash('success', sprintf('Reservation #%d supprimee.', $id));

        return $this->redirectToRoute('app_dashboard', ['view' => 'reservations']);
    }

    #[Route('/dashboard/payments/{id}/delete', name: 'app_dashboard_payment_delete', methods: ['POST'])]
    public function deletePayment(int $id, PaiementRepository $paiementRepository): RedirectResponse
    {
        $paiementRepository->delete($id);
        $this->addFlash('success', sprintf('Paiement #%d supprime.', $id));

        return $this->redirectToRoute('app_dashboard', ['view' => 'payments']);
    }

    private function nullableString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
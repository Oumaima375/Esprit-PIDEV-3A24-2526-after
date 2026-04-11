<?php

namespace App\Controller;

use App\Entity\Paiement;
use App\Entity\Reservation;
use App\Repository\PaiementRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin', methods: ['GET'])]
    public function index(Request $request, ReservationRepository $reservationRepository, PaiementRepository $paiementRepository): Response
    {
        $tab = $request->query->get('tab', 'reservations');
        if (!in_array($tab, ['reservations', 'payments', 'statistics'], true)) {
            $tab = 'reservations';
        }

        $reservations = $reservationRepository->findAll();
        $payments = $paiementRepository->findAll();
        $reservationFilters = [
            'q' => trim((string) $request->query->get('reservation_q', '')),
            'status' => trim((string) $request->query->get('reservation_status', '')),
            'sort' => trim((string) $request->query->get('reservation_sort', 'date')),
            'dir' => strtolower((string) $request->query->get('reservation_dir', 'desc')) === 'asc' ? 'asc' : 'desc',
        ];

        $paymentFilters = [
            'q' => trim((string) $request->query->get('payment_q', '')),
            'status' => trim((string) $request->query->get('payment_status', '')),
            'method' => trim((string) $request->query->get('payment_method', '')),
            'sort' => trim((string) $request->query->get('payment_sort', 'date')),
            'dir' => strtolower((string) $request->query->get('payment_dir', 'desc')) === 'asc' ? 'asc' : 'desc',
        ];

        $filteredReservations = $this->filterReservations($reservations, $reservationFilters);
        $filteredPayments = $this->filterPayments($payments, $paymentFilters);

        return $this->render('admin/index.html.twig', [
            'tab' => $tab,
            'reservations' => $filteredReservations,
            'payments' => $filteredPayments,
            'reservationCount' => count($filteredReservations),
            'paymentCount' => count($filteredPayments),
            'reservationTotalCount' => count($reservations),
            'paymentTotalCount' => count($payments),
            'reservationFilters' => $reservationFilters,
            'paymentFilters' => $paymentFilters,
            'stats' => $this->buildStatistics($reservations, $payments),
        ]);
    }

    #[Route('/admin/reservations/{id}/edit', name: 'app_admin_reservation_edit', methods: ['GET', 'POST'])]
    public function editReservation(int $id, Request $request, ReservationRepository $reservationRepository): Response|RedirectResponse
    {
        $reservation = $reservationRepository->findById($id);
        if (!$reservation) {
            throw $this->createNotFoundException('Reservation introuvable.');
        }

        $returnToDashboard = $request->query->get('return') === 'dashboard';
        $cancelPath = $returnToDashboard
            ? $this->generateUrl('app_dashboard', ['view' => 'reservations'])
            : $this->generateUrl('app_admin', ['tab' => 'reservations']);
        $submitPath = $returnToDashboard
            ? $this->generateUrl('app_admin_reservation_edit', ['id' => $id, 'return' => 'dashboard'])
            : $this->generateUrl('app_admin_reservation_edit', ['id' => $id]);

        if ($request->isMethod('POST')) {
            $reservationRepository->update(
                $id,
                (int) $request->request->get('voyage_id', 0),
                (int) $request->request->get('utilisateur_id', 0),
                $this->nullableString($request->request->get('type')),
                $this->nullableString($request->request->get('lieu')),
                $this->nullableString($request->request->get('description')),
                (string) $request->request->get('date_reservation', ''),
                (string) $request->request->get('statut', 'En attente'),
                (int) $request->request->get('nb_personnes', 0),
                (float) $request->request->get('prix_total', 0),
            );

            $this->addFlash('success', sprintf('Reservation #%d mise a jour avec succes.', $id));

            if ($returnToDashboard) {
                return $this->redirectToRoute('app_dashboard', ['view' => 'reservations']);
            }

            return $this->redirectToRoute('app_admin', ['tab' => 'reservations']);
        }

        return $this->render('admin/edit_reservation.html.twig', [
            'reservation' => $reservation,
            'cancelPath' => $cancelPath,
            'submitPath' => $submitPath,
        ]);
    }

    #[Route('/admin/reservations/{id}/delete', name: 'app_admin_reservation_delete', methods: ['POST'])]
    public function deleteReservation(int $id, ReservationRepository $reservationRepository): RedirectResponse
    {
        $reservationRepository->delete($id);
        $this->addFlash('success', sprintf('Reservation #%d supprimee.', $id));

        return $this->redirectToRoute('app_admin', ['tab' => 'reservations']);
    }

    #[Route('/admin/payments/{id}/edit', name: 'app_admin_payment_edit', methods: ['GET', 'POST'])]
    public function editPayment(int $id, Request $request, PaiementRepository $paiementRepository): Response|RedirectResponse
    {
        $payment = $paiementRepository->findById($id);
        if (!$payment) {
            throw $this->createNotFoundException('Paiement introuvable.');
        }

        $returnToDashboard = $request->query->get('return') === 'dashboard';
        $cancelPath = $returnToDashboard
            ? $this->generateUrl('app_dashboard', ['view' => 'payments'])
            : $this->generateUrl('app_admin', ['tab' => 'payments']);
        $submitPath = $returnToDashboard
            ? $this->generateUrl('app_admin_payment_edit', ['id' => $id, 'return' => 'dashboard'])
            : $this->generateUrl('app_admin_payment_edit', ['id' => $id]);

        if ($request->isMethod('POST')) {
            $paiementRepository->update(
                $id,
                (string) $request->request->get('reference', ''),
                (float) $request->request->get('montant', 0),
                (string) $request->request->get('devise', 'TND'),
                (string) $request->request->get('methode', 'Carte'),
                (string) $request->request->get('statut', 'En attente'),
                (string) $request->request->get('date_paiement', ''),
                (int) $request->request->get('id_reservation', 0),
            );

            $this->addFlash('success', sprintf('Paiement #%d mis a jour avec succes.', $id));

            if ($returnToDashboard) {
                return $this->redirectToRoute('app_dashboard', ['view' => 'payments']);
            }

            return $this->redirectToRoute('app_admin', ['tab' => 'payments']);
        }

        return $this->render('admin/edit_payment.html.twig', [
            'payment' => $payment,
            'cancelPath' => $cancelPath,
            'submitPath' => $submitPath,
        ]);
    }

    #[Route('/admin/payments/{id}/delete', name: 'app_admin_payment_delete', methods: ['POST'])]
    public function deletePayment(int $id, PaiementRepository $paiementRepository): RedirectResponse
    {
        $paiementRepository->delete($id);
        $this->addFlash('success', sprintf('Paiement #%d supprime.', $id));

        return $this->redirectToRoute('app_admin', ['tab' => 'payments']);
    }

    /**
     * @param Reservation[] $reservations
     * @return Reservation[]
     */
    private function filterReservations(array $reservations, array $filters): array
    {
        $filtered = array_filter($reservations, function (Reservation $reservation) use ($filters): bool {
            $query = $this->normalize($filters['q'] ?? '');
            $status = trim((string) ($filters['status'] ?? ''));

            $haystack = $this->normalize(implode(' ', [
                $reservation->getId(),
                $reservation->getType() ?? '',
                $reservation->getLieu() ?? '',
                $reservation->getDateReservation(),
                $reservation->getVoyageId(),
                $reservation->getUtilisateurId(),
                $reservation->getStatut(),
                $reservation->getDescription() ?? '',
            ]));

            $matchesQuery = $query === '' || str_contains($haystack, $query);
            $matchesStatus = $status === '' || $this->mapReservationStatus($reservation->getStatut()) === $status;

            return $matchesQuery && $matchesStatus;
        });

        usort($filtered, function (Reservation $a, Reservation $b) use ($filters): int {
            $field = $filters['sort'] ?? 'date';
            $direction = ($filters['dir'] ?? 'desc') === 'asc' ? 1 : -1;

            $valueA = match ($field) {
                'id' => $a->getId(),
                'type' => $a->getType() ?? '',
                'lieu' => $a->getLieu() ?? '',
                'voyage' => $a->getVoyageId(),
                'client' => $a->getUtilisateurId(),
                'status' => $a->getStatut(),
                'people' => $a->getNbPersonnes(),
                'price' => $a->getPrixTotal(),
                default => $a->getDateReservation(),
            };

            $valueB = match ($field) {
                'id' => $b->getId(),
                'type' => $b->getType() ?? '',
                'lieu' => $b->getLieu() ?? '',
                'voyage' => $b->getVoyageId(),
                'client' => $b->getUtilisateurId(),
                'status' => $b->getStatut(),
                'people' => $b->getNbPersonnes(),
                'price' => $b->getPrixTotal(),
                default => $b->getDateReservation(),
            };

            return $direction * ($valueA <=> $valueB);
        });

        return $filtered;
    }

    /**
     * @param Paiement[] $payments
     * @return Paiement[]
     */
    private function filterPayments(array $payments, array $filters): array
    {
        $filtered = array_filter($payments, function (Paiement $payment) use ($filters): bool {
            $query = $this->normalize($filters['q'] ?? '');
            $status = trim((string) ($filters['status'] ?? ''));
            $method = trim((string) ($filters['method'] ?? ''));

            $haystack = $this->normalize(implode(' ', [
                $payment->getId(),
                $payment->getReference(),
                $payment->getReservationId(),
                $payment->getReservationLieu() ?? '',
                $payment->getStatut(),
                $payment->getMethode(),
                $payment->getDatePaiement() ?? '',
                $payment->getDevise(),
                $payment->getMontant(),
            ]));

            $matchesQuery = $query === '' || str_contains($haystack, $query);
            $matchesStatus = $status === '' || $this->mapPaymentStatus($payment->getStatut()) === $status;
            $matchesMethod = $method === '' || $this->mapPaymentMethod($payment->getMethode()) === $method;

            return $matchesQuery && $matchesStatus && $matchesMethod;
        });

        usort($filtered, function (Paiement $a, Paiement $b) use ($filters): int {
            $field = $filters['sort'] ?? 'date';
            $direction = ($filters['dir'] ?? 'desc') === 'asc' ? 1 : -1;

            $valueA = match ($field) {
                'id' => $a->getId(),
                'reference' => $a->getReference(),
                'amount' => $a->getMontant(),
                'currency' => $a->getDevise(),
                'method' => $a->getMethode(),
                'status' => $a->getStatut(),
                'reservation' => $a->getReservationId(),
                default => $a->getDatePaiement() ?? '',
            };

            $valueB = match ($field) {
                'id' => $b->getId(),
                'reference' => $b->getReference(),
                'amount' => $b->getMontant(),
                'currency' => $b->getDevise(),
                'method' => $b->getMethode(),
                'status' => $b->getStatut(),
                'reservation' => $b->getReservationId(),
                default => $b->getDatePaiement() ?? '',
            };

            return $direction * ($valueA <=> $valueB);
        });

        return $filtered;
    }

    /**
     * @param Reservation[] $reservations
     * @param Paiement[] $payments
     */
    private function buildStatistics(array $reservations, array $payments): array
    {
        $typeCounts = [];
        $timelineCounts = [];
        $monthlyRevenue = [];
        $destinationRevenue = [];
        $pendingReservations = 0;
        $pendingPayments = 0;

        foreach ($reservations as $reservation) {
            $type = $reservation->getType() ?: 'Non renseigne';
            $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;

            $date = $reservation->getDateReservation();
            $timelineCounts[$date] = ($timelineCounts[$date] ?? 0) + 1;

            if (str_contains($this->normalize($reservation->getStatut()), 'attente')) {
                ++$pendingReservations;
            }
        }

        foreach ($payments as $payment) {
            $date = $payment->getDatePaiement() ?? '';
            $month = $date !== '' ? substr($date, 0, 7) : 'Sans date';
            $monthlyRevenue[$month] = ($monthlyRevenue[$month] ?? 0.0) + $payment->getMontant();

            $destination = $payment->getReservationLieu() ?: 'Sans lieu';
            $destinationRevenue[$destination] = ($destinationRevenue[$destination] ?? 0.0) + $payment->getMontant();

            if (str_contains($this->normalize($payment->getStatut()), 'attente')) {
                ++$pendingPayments;
            }
        }

        ksort($timelineCounts);
        ksort($monthlyRevenue);
        arsort($destinationRevenue);

        $runningTotal = 0;
        $reservationTimeline = [];
        foreach ($timelineCounts as $date => $count) {
            $runningTotal += $count;
            $reservationTimeline[] = ['label' => $date, 'value' => $runningTotal];
        }

        $colors = ['#f97316', '#f59e0b', '#22c55e', '#06b6d4', '#4f46e5', '#ec4899'];
        $typeBreakdown = [];
        $i = 0;
        foreach ($typeCounts as $label => $count) {
            $typeBreakdown[] = ['label' => $label, 'count' => $count, 'color' => $colors[$i % count($colors)]];
            ++$i;
        }

        $monthlyRevenueChart = [];
        foreach ($monthlyRevenue as $label => $amount) {
            $monthlyRevenueChart[] = ['label' => $label, 'value' => $amount];
        }

        $topDestinations = [];
        foreach (array_slice($destinationRevenue, 0, 5, true) as $label => $amount) {
            $topDestinations[] = ['label' => $label, 'value' => $amount];
        }

        $totalRevenue = array_sum(array_map(static fn (Paiement $payment): float => $payment->getMontant(), $payments));
        $averageTicket = count($payments) > 0 ? $totalRevenue / count($payments) : 0.0;

        return [
            'reservationCount' => count($reservations),
            'paymentCount' => count($payments),
            'totalRevenue' => $totalRevenue,
            'pendingReservations' => $pendingReservations,
            'pendingPayments' => $pendingPayments,
            'averageTicket' => $averageTicket,
            'typeBreakdown' => $typeBreakdown,
            'reservationTimeline' => $reservationTimeline,
            'monthlyRevenue' => $monthlyRevenueChart,
            'topDestinations' => $topDestinations,
        ];
    }

    private function mapReservationStatus(string $status): string
    {
        $normalized = $this->normalize($status);

        return match (true) {
            str_contains($normalized, 'confirm') => 'confirmed',
            str_contains($normalized, 'annul') => 'cancelled',
            str_contains($normalized, 'attente') => 'pending',
            default => 'other',
        };
    }

    private function mapPaymentStatus(string $status): string
    {
        $normalized = $this->normalize($status);

        return match (true) {
            str_contains($normalized, 'pay') => 'paid',
            str_contains($normalized, 'rembours') => 'refunded',
            str_contains($normalized, 'attente') => 'pending',
            default => 'other',
        };
    }

    private function mapPaymentMethod(string $method): string
    {
        $normalized = $this->normalize($method);

        return match (true) {
            str_contains($normalized, 'carte') => 'card',
            str_contains($normalized, 'virement') => 'transfer',
            str_contains($normalized, 'espec') => 'cash',
            default => 'other',
        };
    }

    private function normalize(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($converted !== false && $converted !== '') {
            $value = $converted;
        }

        return strtolower($value);
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
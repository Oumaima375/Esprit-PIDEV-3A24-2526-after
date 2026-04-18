<?php

namespace App\Controller;

use App\Entity\DocumentArchive;
use App\Entity\Paiement;
use App\Entity\Reservation;
use App\Repository\ActiviteRepository;
use App\Repository\CategorieDocumentRepository;
use App\Repository\DepenseRepository;
use App\Repository\DestinationRepository;
use App\Repository\DocumentArchiveRepository;
use App\Repository\DocumentRepository;
use App\Repository\OffreRepository;
use App\Repository\PaiementRepository;
use App\Repository\ReservationRepository;
use App\Repository\ServiceRepository;
use App\Repository\UsersRepository;
use App\Repository\VoyageRepository;
use App\Service\CategorieDetectorService;
use App\Service\CloudinaryService;
use App\Service\MailjetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Document;
use App\Form\DocumentType;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    /**
     * Unified admin dashboard — feeds ALL sections in one request
     * so the single-page template can switch tabs without reloading.
     */
    #[Route('', name: 'app_admin', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        ReservationRepository $reservationRepository,
        PaiementRepository $paiementRepository,
        DocumentRepository $documentRepository,
        CategorieDocumentRepository $categorieDocumentRepository,
        EntityManagerInterface $entityManager,
        CategorieDetectorService $categorieDetector,
        CloudinaryService $cloudinaryService,
        UsersRepository $usersRepository,
        VoyageRepository $voyageRepository,
        DestinationRepository $destinationRepository,
        ActiviteRepository $activiteRepository,
        ServiceRepository $serviceRepository,
        OffreRepository $offreRepository,
        DepenseRepository $depenseRepository
    ): Response {
        // ── Reservation filters ──
        $reservationFilters = [
            'q'      => trim((string) $request->query->get('reservation_q', '')),
            'status' => trim((string) $request->query->get('reservation_status', '')),
            'sort'   => trim((string) $request->query->get('reservation_sort', 'date')),
            'dir'    => strtolower((string) $request->query->get('reservation_dir', 'desc')) === 'asc' ? 'asc' : 'desc',
        ];

        $paymentFilters = [
            'q'      => trim((string) $request->query->get('payment_q', '')),
            'status' => trim((string) $request->query->get('payment_status', '')),
            'method' => trim((string) $request->query->get('payment_method', '')),
            'sort'   => trim((string) $request->query->get('payment_sort', 'date')),
            'dir'    => strtolower((string) $request->query->get('payment_dir', 'desc')) === 'asc' ? 'asc' : 'desc',
        ];

        $allReservations = $reservationRepository->findAll();
        $allPayments     = $paiementRepository->findAll();
        $filteredRes     = $this->filterReservations($allReservations, $reservationFilters);
        $filteredPay     = $this->filterPayments($allPayments, $paymentFilters);

        // ── Documents ──
        $allDocuments = $documentRepository->findAll();
        $expiredDocs  = $documentRepository->findExpired();
        $categories   = $categorieDocumentRepository->findAll();

        // ── Document new form (POST) ──
        $newDocument  = new Document();
        $documentForm = $this->createForm(DocumentType::class, $newDocument);
        $documentForm->handleRequest($request);

        if ($documentForm->isSubmitted() && $documentForm->isValid()) {
            $libelleDetecte = null;
            if (!$newDocument->getCategorie()) {
                $cats = array_map(fn($c) => $c->getLibelle(), $categorieDocumentRepository->findAll());
                $libelleDetecte = $categorieDetector->detecterCategorie($newDocument->getNomDocument(), $cats);
                if ($libelleDetecte) {
                    $cat = $categorieDocumentRepository->findOneBy(['libelle' => $libelleDetecte]);
                    if ($cat) {
                        $newDocument->setCategorie($cat);
                    }
                }
            }
            $fichier = $documentForm->get('fichier')->getData();
            if ($fichier) {
                try {
                    $url = $cloudinaryService->upload($fichier->getRealPath(), $fichier->getClientOriginalName());
                    $newDocument->setCheminFichier($url);
                } catch (\Exception $e) {
                    $nom = uniqid() . '.' . $fichier->guessExtension();
                    $fichier->move($this->getParameter('uploads_directory'), $nom);
                    $newDocument->setCheminFichier($nom);
                }
            } else {
                $newDocument->setCheminFichier('aucun_fichier');
            }
            $entityManager->persist($newDocument);
            $entityManager->flush();
            $msg = $libelleDetecte
                ? '✅ Document ajouté ! 🤖 Catégorie : ' . $libelleDetecte
                : '✅ Document ajouté !';
            $this->addFlash('success', $msg);
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/dashboard.html.twig', [
            // ── Auth ──
            // app.user is accessible directly in Twig via app global

            // ── Reservations ──
            'reservations'           => $filteredRes,
            'reservationCount'       => count($filteredRes),
            'reservationTotalCount'  => count($allReservations),
            'reservationFilters'     => $reservationFilters,

            // ── Payments ──
            'payments'               => $filteredPay,
            'paymentCount'           => count($filteredPay),
            'paymentTotalCount'      => count($allPayments),
            'paymentFilters'         => $paymentFilters,

            // ── Statistics ──
            'stats'                  => $this->buildStatistics($allReservations, $allPayments),

            // ── Documents ──
            'allDocuments'           => $allDocuments,
            'docTotal'               => count($allDocuments),
            'docValides'             => count($allDocuments) - count($expiredDocs),
            'docExpires'             => count($expiredDocs),
            'categories'             => $categories,
            'parCategorie'           => $documentRepository->countByCategorie(),
            'documentForm'           => $documentForm->createView(),

            // ── Users ──
            'users'                  => $usersRepository->findAll(),

            // ── Voyages & Destinations ──
            'voyages'                => $voyageRepository->findBy([], ['id_voyage' => 'DESC']),
            'destinations'           => $destinationRepository->findAll(),

            // ── Activités ──
            'activites'              => $activiteRepository->findAll(),

            // ── Services & Offres ──
            'services'               => $serviceRepository->findAll(),
            'offres'                 => $offreRepository->findAll(),

            // ── Dépenses ──
            'depenses'               => $depenseRepository->findAll(),
        ]);
    }

    // ─── Keep existing sub-routes for dedicated pages ───────────────────────────

    #[Route('/reservations', name: 'app_admin_reservations', methods: ['GET'])]
    public function reservations(Request $request, ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findAll();
        $filters = [
            'q'      => trim((string) $request->query->get('reservation_q', '')),
            'status' => trim((string) $request->query->get('reservation_status', '')),
            'sort'   => trim((string) $request->query->get('reservation_sort', 'date')),
            'dir'    => strtolower((string) $request->query->get('reservation_dir', 'desc')) === 'asc' ? 'asc' : 'desc',
        ];
        return $this->render('admin/reservations/index.html.twig', [
            'reservations'           => $this->filterReservations($reservations, $filters),
            'reservationCount'       => count($this->filterReservations($reservations, $filters)),
            'reservationTotalCount'  => count($reservations),
            'reservationFilters'     => $filters,
        ]);
    }

    #[Route('/paiements', name: 'app_admin_payments', methods: ['GET'])]
    public function payments(Request $request, PaiementRepository $paiementRepository): Response
    {
        $payments = $paiementRepository->findAll();
        $filters = [
            'q'      => trim((string) $request->query->get('payment_q', '')),
            'status' => trim((string) $request->query->get('payment_status', '')),
            'method' => trim((string) $request->query->get('payment_method', '')),
            'sort'   => trim((string) $request->query->get('payment_sort', 'date')),
            'dir'    => strtolower((string) $request->query->get('payment_dir', 'desc')) === 'asc' ? 'asc' : 'desc',
        ];
        return $this->render('admin/payments/index.html.twig', [
            'payments'           => $this->filterPayments($payments, $filters),
            'paymentCount'       => count($this->filterPayments($payments, $filters)),
            'paymentTotalCount'  => count($payments),
            'paymentFilters'     => $filters,
        ]);
    }

    #[Route('/documents', name: 'app_admin_documents', methods: ['GET'])]
    public function documents(DocumentRepository $documentRepository, CategorieDocumentRepository $catRepo): Response
    {
        $allDocs = $documentRepository->findAll();
        $expired = $documentRepository->findExpired();
        return $this->render('admin/documents/index.html.twig', [
            'allDocuments' => $allDocs,
            'docTotal'     => count($allDocs),
            'docValides'   => count($allDocs) - count($expired),
            'docExpires'   => count($expired),
        ]);
    }

    #[Route('/documents/nouveau', name: 'app_admin_document_new', methods: ['GET', 'POST'])]
    public function documentNew(
        Request $request,
        CategorieDocumentRepository $categorieDocumentRepository,
        EntityManagerInterface $entityManager,
        CategorieDetectorService $categorieDetector,
        CloudinaryService $cloudinaryService
    ): Response {
        $newDocument  = new Document();
        $documentForm = $this->createForm(DocumentType::class, $newDocument);
        $documentForm->handleRequest($request);

        if ($documentForm->isSubmitted() && $documentForm->isValid()) {
            if (!$newDocument->getCategorie()) {
                $cats           = array_map(fn($c) => $c->getLibelle(), $categorieDocumentRepository->findAll());
                $libelleDetecte = $categorieDetector->detecterCategorie($newDocument->getNomDocument(), $cats);
                if ($libelleDetecte) {
                    $cat = $categorieDocumentRepository->findOneBy(['libelle' => $libelleDetecte]);
                    if ($cat) {
                        $newDocument->setCategorie($cat);
                    }
                }
            }
            $fichier = $documentForm->get('fichier')->getData();
            if ($fichier) {
                try {
                    $url = $cloudinaryService->upload($fichier->getRealPath(), $fichier->getClientOriginalName());
                    $newDocument->setCheminFichier($url);
                } catch (\Exception $e) {
                    $nom = uniqid() . '.' . $fichier->guessExtension();
                    $fichier->move($this->getParameter('uploads_directory'), $nom);
                    $newDocument->setCheminFichier($nom);
                }
            } else {
                $newDocument->setCheminFichier('aucun_fichier');
            }
            $entityManager->persist($newDocument);
            $entityManager->flush();
            $this->addFlash('success', '✅ Document ajouté !');
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/documents/new.html.twig', [
            'documentForm' => $documentForm->createView(),
        ]);
    }

    #[Route('/documents/statistiques', name: 'app_admin_document_stats', methods: ['GET'])]
    public function documentStats(DocumentRepository $documentRepository): Response
    {
        $allDocs = $documentRepository->findAll();
        $expired = $documentRepository->findExpired();
        return $this->render('admin/documents/stats.html.twig', [
            'docTotal'     => count($allDocs),
            'docValides'   => count($allDocs) - count($expired),
            'docExpires'   => count($expired),
            'parCategorie' => $documentRepository->countByCategorie(),
        ]);
    }

    #[Route('/documents/categories', name: 'app_admin_categories', methods: ['GET'])]
    public function categories(CategorieDocumentRepository $categorieDocumentRepository): Response
    {
        return $this->render('admin/documents/categories.html.twig', [
            'categories' => $categorieDocumentRepository->findAll(),
        ]);
    }

    #[Route('/archiver-expires', name: 'app_admin_archiver_expires', methods: ['GET'])]
    public function archiverExpires(
        DocumentRepository $documentRepository,
        EntityManagerInterface $entityManager,
        MailjetService $mailjetService
    ): Response {
        $expires = $documentRepository->findExpired();
        $count   = 0;
        foreach ($expires as $doc) {
            $mailjetService->envoyerNotificationExpiration(
                'voyageur@example.com',
                $doc->getNomDocument(),
                $doc->getDateExpiration()?->format('d/m/Y') ?? 'N/A'
            );
            $archive = new DocumentArchive();
            $archive->setNomDocument($doc->getNomDocument());
            $archive->setCheminFichier($doc->getCheminFichier());
            $archive->setDateAjout($doc->getDateAjout());
            $archive->setDateExpiration($doc->getDateExpiration());
            $archive->setCategorie($doc->getCategorie()?->getLibelle() ?? 'N/A');
            $archive->setRaison('Expiré - Supprimé par admin');
            $entityManager->persist($archive);
            $entityManager->remove($doc);
            $count++;
        }
        $entityManager->flush();
        $this->addFlash('success', '✅ ' . $count . ' documents archivés ! Emails envoyés.');
        return $this->redirectToRoute('app_admin');
    }

    #[Route('/archives', name: 'app_admin_archives')]
    public function archives(DocumentArchiveRepository $archiveRepository): Response
    {
        return $this->render('admin/archives.html.twig', [
            'archives' => $archiveRepository->findBy([], ['dateArchivage' => 'DESC']),
        ]);
    }

    #[Route('/reservations/{id}/edit', name: 'app_admin_reservation_edit', methods: ['GET', 'POST'])]
    public function editReservation(int $id, Request $request, ReservationRepository $reservationRepository): Response|RedirectResponse
    {
        $reservation = $reservationRepository->findById($id);
        if (!$reservation) {
            throw $this->createNotFoundException('Reservation introuvable.');
        }
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
            $this->addFlash('success', sprintf('Reservation #%d mise à jour.', $id));
            return $this->redirectToRoute('app_admin');
        }
        return $this->render('admin/edit_reservation.html.twig', [
            'reservation' => $reservation,
            'cancelPath'  => $this->generateUrl('app_admin'),
            'submitPath'  => $this->generateUrl('app_admin_reservation_edit', ['id' => $id]),
        ]);
    }

    #[Route('/reservations/{id}/delete', name: 'app_admin_reservation_delete', methods: ['POST'])]
    public function deleteReservation(int $id, ReservationRepository $reservationRepository): RedirectResponse
    {
        $reservationRepository->delete($id);
        $this->addFlash('success', sprintf('Reservation #%d supprimée.', $id));
        return $this->redirectToRoute('app_admin');
    }

    #[Route('/payments/{id}/edit', name: 'app_admin_payment_edit', methods: ['GET', 'POST'])]
    public function editPayment(int $id, Request $request, PaiementRepository $paiementRepository): Response|RedirectResponse
    {
        $payment = $paiementRepository->findById($id);
        if (!$payment) {
            throw $this->createNotFoundException('Paiement introuvable.');
        }
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
            $this->addFlash('success', sprintf('Paiement #%d mis à jour.', $id));
            return $this->redirectToRoute('app_admin');
        }
        return $this->render('admin/edit_payment.html.twig', [
            'payment'     => $payment,
            'cancelPath'  => $this->generateUrl('app_admin'),
            'submitPath'  => $this->generateUrl('app_admin_payment_edit', ['id' => $id]),
        ]);
    }

    #[Route('/payments/{id}/delete', name: 'app_admin_payment_delete', methods: ['POST'])]
    public function deletePayment(int $id, PaiementRepository $paiementRepository): RedirectResponse
    {
        $paiementRepository->delete($id);
        $this->addFlash('success', sprintf('Paiement #%d supprimé.', $id));
        return $this->redirectToRoute('app_admin');
    }

    // ─── Private helpers ────────────────────────────────────────────────────────

    private function filterReservations(array $reservations, array $filters): array
    {
        $filtered = array_filter($reservations, function (Reservation $reservation) use ($filters): bool {
            $query  = $this->normalize($filters['q'] ?? '');
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

            $matchesQuery  = $query === '' || str_contains($haystack, $query);
            $matchesStatus = $status === '' || $this->mapReservationStatus($reservation->getStatut()) === $status;

            return $matchesQuery && $matchesStatus;
        });

        usort($filtered, function (Reservation $a, Reservation $b) use ($filters): int {
            $field     = $filters['sort'] ?? 'date';
            $direction = ($filters['dir'] ?? 'desc') === 'asc' ? 1 : -1;

            $valueA = match ($field) {
                'id'     => $a->getId(),
                'type'   => $a->getType() ?? '',
                'lieu'   => $a->getLieu() ?? '',
                'voyage' => $a->getVoyageId(),
                'client' => $a->getUtilisateurId(),
                'status' => $a->getStatut(),
                'people' => $a->getNbPersonnes(),
                'price'  => $a->getPrixTotal(),
                default  => $a->getDateReservation(),
            };
            $valueB = match ($field) {
                'id'     => $b->getId(),
                'type'   => $b->getType() ?? '',
                'lieu'   => $b->getLieu() ?? '',
                'voyage' => $b->getVoyageId(),
                'client' => $b->getUtilisateurId(),
                'status' => $b->getStatut(),
                'people' => $b->getNbPersonnes(),
                'price'  => $b->getPrixTotal(),
                default  => $b->getDateReservation(),
            };

            return $direction * ($valueA <=> $valueB);
        });

        return $filtered;
    }

    private function filterPayments(array $payments, array $filters): array
    {
        $filtered = array_filter($payments, function (Paiement $payment) use ($filters): bool {
            $query  = $this->normalize($filters['q'] ?? '');
            $status = trim((string) ($filters['status'] ?? ''));
            $method = trim((string) ($filters['method'] ?? ''));

            $haystack = $this->normalize(implode(' ', [
                $payment->getId(), $payment->getReference(),
                $payment->getReservationId(), $payment->getReservationLieu() ?? '',
                $payment->getStatut(), $payment->getMethode(),
                $payment->getDatePaiement() ?? '', $payment->getDevise(), $payment->getMontant(),
            ]));

            $matchesQuery  = $query === '' || str_contains($haystack, $query);
            $matchesStatus = $status === '' || $this->mapPaymentStatus($payment->getStatut()) === $status;
            $matchesMethod = $method === '' || $this->mapPaymentMethod($payment->getMethode()) === $method;

            return $matchesQuery && $matchesStatus && $matchesMethod;
        });

        usort($filtered, function (Paiement $a, Paiement $b) use ($filters): int {
            $field     = $filters['sort'] ?? 'date';
            $direction = ($filters['dir'] ?? 'desc') === 'asc' ? 1 : -1;

            $valueA = match ($field) {
                'id'          => $a->getId(),
                'reference'   => $a->getReference(),
                'amount'      => $a->getMontant(),
                'currency'    => $a->getDevise(),
                'method'      => $a->getMethode(),
                'status'      => $a->getStatut(),
                'reservation' => $a->getReservationId(),
                default       => $a->getDatePaiement() ?? '',
            };
            $valueB = match ($field) {
                'id'          => $b->getId(),
                'reference'   => $b->getReference(),
                'amount'      => $b->getMontant(),
                'currency'    => $b->getDevise(),
                'method'      => $b->getMethode(),
                'status'      => $b->getStatut(),
                'reservation' => $b->getReservationId(),
                default       => $b->getDatePaiement() ?? '',
            };

            return $direction * ($valueA <=> $valueB);
        });

        return $filtered;
    }

    private function buildStatistics(array $reservations, array $payments): array
    {
        $typeCounts         = [];
        $timelineCounts     = [];
        $monthlyRevenue     = [];
        $destinationRevenue = [];
        $pendingReservations = 0;
        $pendingPayments     = 0;

        foreach ($reservations as $reservation) {
            $type = $reservation->getType() ?: 'Non renseigné';
            $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;
            $date = $reservation->getDateReservation();
            $timelineCounts[$date] = ($timelineCounts[$date] ?? 0) + 1;
            if (str_contains($this->normalize($reservation->getStatut()), 'attente')) {
                ++$pendingReservations;
            }
        }

        foreach ($payments as $payment) {
            $date  = $payment->getDatePaiement() ?? '';
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

        $totalRevenue  = array_sum(array_map(static fn(Paiement $p): float => $p->getMontant(), $payments));
        $averageTicket = count($payments) > 0 ? $totalRevenue / count($payments) : 0.0;

        return [
            'reservationCount'    => count($reservations),
            'paymentCount'        => count($payments),
            'totalRevenue'        => $totalRevenue,
            'pendingReservations' => $pendingReservations,
            'pendingPayments'     => $pendingPayments,
            'averageTicket'       => $averageTicket,
            'typeBreakdown'       => $typeBreakdown,
            'reservationTimeline' => $reservationTimeline,
            'monthlyRevenue'      => $monthlyRevenueChart,
            'topDestinations'     => $topDestinations,
        ];
    }

    private function mapReservationStatus(string $status): string
    {
        $n = $this->normalize($status);
        return match (true) {
            str_contains($n, 'confirm') => 'confirmed',
            str_contains($n, 'annul')   => 'cancelled',
            str_contains($n, 'attente') => 'pending',
            default                     => 'other',
        };
    }

    private function mapPaymentStatus(string $status): string
    {
        $n = $this->normalize($status);
        return match (true) {
            str_contains($n, 'pay')      => 'paid',
            str_contains($n, 'rembours') => 'refunded',
            str_contains($n, 'attente')  => 'pending',
            default                      => 'other',
        };
    }

    private function mapPaymentMethod(string $method): string
    {
        $n = $this->normalize($method);
        return match (true) {
            str_contains($n, 'carte')    => 'card',
            str_contains($n, 'virement') => 'transfer',
            str_contains($n, 'espec')    => 'cash',
            default                      => 'other',
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
<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\DocumentHistorique;
use App\Form\DocumentType;
use App\Repository\CategorieDocumentRepository;
use App\Repository\DocumentHistoriqueRepository;
use App\Repository\DocumentRepository;
use App\Service\CategorieDetectorService;
use App\Service\CloudinaryService;
use App\Service\ConseilsService;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// TODO after merge — décommentez après intégration User
// use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/document/crud')]
final class DocumentCrudController extends AbstractController
{
    // ===== INDEX =====
    #[Route('', name: 'app_document_crud_index', methods: ['GET'])]
    // TODO after merge — décommentez pour forcer la connexion
    // #[IsGranted('ROLE_USER')]
    public function index(
        Request $request,
        DocumentRepository $documentRepository,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('search', '');
        $filtre = $request->query->get('filtre', 'tous');
        $tri    = $request->query->get('tri', 'dateAjout');
        $ordre  = $request->query->get('ordre', 'ASC');

        // TODO after merge — remplacez findByFiltersQuery par findByFiltersQueryForUser
        // if ($this->isGranted('ROLE_ADMIN')) {
        //     $query = $documentRepository->findByFiltersQuery($search, $filtre, $tri, $ordre);
        // } else {
        //     $query = $documentRepository->findByFiltersQueryForUser($search, $filtre, $tri, $ordre, $this->getUser());
        // }
        $query = $documentRepository->findByFiltersQuery($search, $filtre, $tri, $ordre);

        $documents = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );

        $allDocs = $documentRepository->findAll();
        $expires = $documentRepository->findExpired();

        // TODO after merge — remplacez findAll() par findByUser()
        // $allDocs = $documentRepository->findByUser($this->getUser());
        // $expires = $documentRepository->findExpiredByUser($this->getUser());

        return $this->render('document_crud/index.html.twig', [
            'documents' => $documents,
            'total'     => count($allDocs),
            'valides'   => count($allDocs) - count($expires),
            'expires'   => count($expires),
            'search'    => $search,
        ]);
    }

    // ===== NEW =====
    #[Route('/new', name: 'app_document_crud_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        CategorieDetectorService $categorieDetector,
        CategorieDocumentRepository $categorieRepo,
        CloudinaryService $cloudinaryService
    ): Response {
        $document = new Document();
        $form     = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $libelleDetecte = null;
            if (!$document->getCategorie()) {
                $categories = array_map(
                    fn($c) => $c->getLibelle(),
                    $categorieRepo->findAll()
                );
                $libelleDetecte = $categorieDetector->detecterCategorie(
                    $document->getNomDocument(),
                    $categories
                );
                if ($libelleDetecte) {
                    $categorie = $categorieRepo->findOneBy(['libelle' => $libelleDetecte]);
                    if ($categorie) {
                        $document->setCategorie($categorie);
                    }
                }
            }

            $fichier = $form->get('fichier')->getData();
            if ($fichier) {
                try {
                    $url = $cloudinaryService->upload(
                        $fichier->getRealPath(),
                        $fichier->getClientOriginalName()
                    );
                    $document->setCheminFichier($url);
                } catch (\Exception $e) {
                    $nomFichier = uniqid() . '.' . $fichier->guessExtension();
                    $fichier->move($this->getParameter('uploads_directory'), $nomFichier);
                    $document->setCheminFichier($nomFichier);
                }
            } else {
                $document->setCheminFichier('aucun_fichier');
            }

            $entityManager->persist($document);
            $entityManager->flush();

            // ← Historique création SEULEMENT
            $historique = new DocumentHistorique();
            $historique->setIdDocument($document->getIdDocument());
            $historique->setNomDocument($document->getNomDocument());
            $historique->setAction('création');
            $historique->setNouvellesValeurs([
                'nomDocument'    => $document->getNomDocument(),
                'categorie'      => $document->getCategorie()?->getLibelle(),
                'dateAjout'      => $document->getDateAjout()?->format('d/m/Y'),
                'dateExpiration' => $document->getDateExpiration()?->format('d/m/Y'),
            ]);
            $entityManager->persist($historique);
            $entityManager->flush();

            if ($libelleDetecte) {
                $this->addFlash('success', '✅ Document ajouté ! 🤖 Catégorie détectée : ' . $libelleDetecte);
            } else {
                $this->addFlash('success', '✅ Document ajouté !');
            }

            return $this->redirectToRoute('app_document_crud_index');
        }

        return $this->render('document_crud/new.html.twig', [
            'document' => $document,
            'form'     => $form,
        ]);
    }

    // ===== QR CODE =====
    #[Route('/{idDocument}/qrcode', name: 'app_document_qrcode', methods: ['GET'])]
    public function qrcode(int $idDocument, Request $request, DocumentRepository $repo): Response
    {
        $document = $repo->find($idDocument);
        if (!$document) {
            throw $this->createNotFoundException('Document non trouvé');
        }

        // TODO after merge — vérifier que le document appartient à l'user connecté
        // if ($document->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
        //     throw $this->createAccessDeniedException('Accès refusé');
        // }

        $contenu = sprintf(
            "AFTER Travel | %s | %s | Ajout: %s | Expire: %s",
            $document->getNomDocument(),
            $document->getCategorie()?->getLibelle() ?? 'N/A',
            $document->getDateAjout()?->format('d/m/Y') ?? 'N/A',
            $document->getDateExpiration()?->format('d/m/Y') ?? 'N/A'
        );

        $writer = new SvgWriter();
        $qrCode = new QrCode(
            data:            $contenu,
            encoding:        new Encoding('UTF-8'),
            size:            300,
            margin:          10,
            foregroundColor: new Color(26, 39, 68),
            backgroundColor: new Color(255, 255, 255)
        );

        $result  = $writer->write($qrCode);
        $svgData = $result->getString();

        $disposition = $request->query->get('download')
            ? 'attachment; filename="qrcode_' . $document->getNomDocument() . '.svg"'
            : 'inline';

        return new Response($svgData, 200, [
            'Content-Type'        => 'image/svg+xml',
            'Content-Length'      => strlen($svgData),
            'Content-Disposition' => $disposition,
            'Cache-Control'       => 'no-cache',
        ]);
    }

    // ===== STATS =====
    #[Route('/stats', name: 'app_document_stats', methods: ['GET'])]
    public function stats(
        DocumentRepository $documentRepository,
        CategorieDocumentRepository $categorieRepo
    ): Response {
        $taux         = $documentRepository->getTauxExpiration();
        $parCategorie = $documentRepository->countByCategorie();
        $topCats      = $documentRepository->getTopCategories();
        $parMois      = $documentRepository->getMonthlyUploads();

        return $this->render('document_crud/stats.html.twig', [
            'total'        => $taux['total'],
            'expires'      => $taux['expires'],
            'valides'      => $taux['valides'],
            'bientot'      => $taux['bientot'],
            'taux'         => $taux,
            'parCategorie' => $parCategorie,
            'topCats'      => $topCats,
            'parMois'      => $parMois,
        ]);
    }
    // ===== EXPORT PDF =====
    #[Route('/export/pdf', name: 'app_document_export_pdf', methods: ['GET'])]
    public function exportPdf(DocumentRepository $documentRepository): Response
    {
        // TODO after merge — exporter seulement les documents de l'user connecté
        // if ($this->isGranted('ROLE_ADMIN')) {
        //     $documents = $documentRepository->findAll();
        // } else {
        //     $documents = $documentRepository->findByUser($this->getUser());
        // }
        $documents = $documentRepository->findAll();

        $html = $this->renderView('document_crud/pdf.html.twig', [
            'documents' => $documents,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="documents_' . date('Y-m-d') . '.pdf"',
            ]
        );
    }

    // ===== AJAX SEARCH =====
    #[Route('/search', name: 'app_document_crud_search', methods: ['GET'])]
    public function search(Request $request, DocumentRepository $documentRepository): Response
    {
        $search = $request->query->get('search', '');
        $filtre = $request->query->get('filtre', 'tous');
        $tri    = $request->query->get('tri', 'dateAjout');
        $ordre  = $request->query->get('ordre', 'ASC');

        // TODO after merge — filtrer par user connecté
        // if ($this->isGranted('ROLE_ADMIN')) {
        //     $documents = $documentRepository->findByFilters($search, $filtre, $tri, $ordre);
        // } else {
        //     $documents = $documentRepository->findByFiltersForUser($search, $filtre, $tri, $ordre, $this->getUser());
        // }
        $documents = $documentRepository->findByFilters($search, $filtre, $tri, $ordre);

        $html = $this->renderView('document_crud/_cards.html.twig', [
            'documents' => $documents,
        ]);

        return $this->json([
            'html'  => $html,
            'count' => count($documents),
        ]);
    }

    // ===== SHOW =====
    #[Route('/{idDocument}', name: 'app_document_crud_show', methods: ['GET'])]
    public function show(
        int $idDocument,
        DocumentRepository $documentRepository,
        ConseilsService $conseilsService,
        DocumentHistoriqueRepository $historiqueRepo
    ): Response {
        $document = $documentRepository->find($idDocument);
       
        // ← ajoutez

        if (!$document) {
            throw $this->createNotFoundException('Document non trouvé');
        }

        // TODO after merge — vérifier accès
        // if ($document->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
        //     throw $this->createAccessDeniedException('Ce document ne vous appartient pas');
        // }

        $conseils   = $conseilsService->genererConseils(
            $document->getNomDocument(),
            $document->getCategorie()?->getLibelle() ?? 'Document',
            $document->getDateExpiration()?->format('d/m/Y')
        );

        $historique = $historiqueRepo->findByDocument($idDocument);

        return $this->render('document_crud/show.html.twig', [
            'document'   => $document,
            'conseils'   => $conseils,
            'historique' => $historique,
        ]);
    }

    // ===== EDIT =====
    #[Route('/{idDocument}/edit', name: 'app_document_crud_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        int $idDocument,
        DocumentRepository $documentRepository,
        EntityManagerInterface $entityManager,
        CloudinaryService $cloudinaryService
    ): Response {
        $document = $documentRepository->find($idDocument);
        if (!$document) {
            throw $this->createNotFoundException('Document non trouvé');
        }

        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // ← Sauvegarder les anciennes valeurs AVANT flush
            $ancienNom  = $document->getNomDocument();
            $ancienDate = $document->getDateExpiration()?->format('d/m/Y');
            $ancienneCat = $document->getCategorie()?->getLibelle();

            $dateAjout      = $document->getDateAjout();
            $dateExpiration = $document->getDateExpiration();

            if ($dateExpiration && $dateAjout && $dateExpiration <= $dateAjout) {
                $form->get('dateExpiration')->addError(
                    new \Symfony\Component\Form\FormError(
                        "La date d'expiration doit être après la date d'ajout."
                    )
                );
                return $this->render('document_crud/edit.html.twig', [
                    'document' => $document,
                    'form'     => $form,
                ]);
            }

            $fichier = $form->get('fichier')->getData();
            if ($fichier) {
                try {
                    $url = $cloudinaryService->upload(
                        $fichier->getRealPath(),
                        $fichier->getClientOriginalName()
                    );
                    $document->setCheminFichier($url);
                } catch (\Exception $e) {
                    $nomFichier = uniqid() . '.' . $fichier->guessExtension();
                    $fichier->move($this->getParameter('uploads_directory'), $nomFichier);
                    $document->setCheminFichier($nomFichier);
                }
            }

            $entityManager->flush();

            // ← Historique modification APRÈS flush
            $historique = new DocumentHistorique();
            $historique->setIdDocument($document->getIdDocument());
            $historique->setNomDocument($document->getNomDocument());
            $historique->setAction('modification');
            $historique->setAnciennesValeurs([
                'nomDocument'    => $ancienNom,
                'categorie'      => $ancienneCat,
                'dateExpiration' => $ancienDate,
            ]);
            $historique->setNouvellesValeurs([
                'nomDocument'    => $document->getNomDocument(),
                'categorie'      => $document->getCategorie()?->getLibelle(),
                'dateExpiration' => $document->getDateExpiration()?->format('d/m/Y'),
            ]);
            $entityManager->persist($historique);
            $entityManager->flush();

            $this->addFlash('success', '✅ Document modifié avec succès !');
            return $this->redirectToRoute('app_document_crud_index');
        }

        return $this->render('document_crud/edit.html.twig', [
            'document' => $document,
            'form'     => $form,
        ]);
    }
    // ===== SUPPRIMER EXPIRES =====
    #[Route('/supprimer/expires', name: 'app_document_supprimer_expires', methods: ['GET'])]
    public function supprimerExpires(
        DocumentRepository $documentRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $expires = $documentRepository->findExpired();
        foreach ($expires as $doc) {
            $entityManager->remove($doc);
        }
        $entityManager->flush();
        $this->addFlash('success', '🗑 ' . count($expires) . ' documents expirés supprimés !');
        return $this->redirectToRoute('app_admin_dashboard');
    }

    // ===== DELETE =====
    #[Route('/{idDocument}', name: 'app_document_crud_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        int $idDocument,
        DocumentRepository $documentRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $document = $documentRepository->find($idDocument);

        // TODO after merge — vérifier que c'est le bon user + bloquer admin
        // if ($document && $document->getUser() !== $this->getUser()) {
        //     throw $this->createAccessDeniedException('Ce document ne vous appartient pas');
        // }
        // if ($this->isGranted('ROLE_ADMIN')) {
        //     $this->addFlash('error', '⚠️ Les admins ne peuvent pas supprimer les documents.');
        //     return $this->redirectToRoute('app_admin_dashboard');
        // }

        if ($document && $this->isCsrfTokenValid(
            'delete' . $idDocument,
            $request->getPayload()->getString('_token')
        )) {
            $entityManager->remove($document);
            $entityManager->flush();
            $this->addFlash('success', '🗑 Document supprimé !');
        }

        $referer = $request->headers->get('referer');
        if ($referer && str_contains($referer, '/admin/dashboard')) {
            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->redirectToRoute('app_document_crud_index');
    }


    //NOTIF//
    #[Route('/notifications/check', name: 'app_notifications_check', methods: ['GET'])]
    public function checkNotifications(DocumentRepository $repo): Response
    {
        $bientot = $repo->findExpiringSoon();
        return $this->json([
            'count'     => count($bientot),
            'documents' => array_map(fn($d) => [
                'nom'            => $d->getNomDocument(),
                'dateExpiration' => $d->getDateExpiration()?->format('d/m/Y'),
                'joursRestants'  => (new \DateTime())->diff($d->getDateExpiration())->days,
            ], $bientot)
        ]);
    }
}
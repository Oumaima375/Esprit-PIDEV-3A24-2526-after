<?php

namespace App\Controller;

use App\Entity\Document;
use App\Form\DocumentType;
use App\Repository\CategorieDocumentRepository;
use App\Repository\DocumentRepository;
use App\Service\CloudinaryService;
use App\Service\ConseilsService;
use App\Service\CategorieDetectorService;
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

#[Route('/document/crud')]
final class DocumentCrudController extends AbstractController
{
    // ===== INDEX =====
    #[Route('', name: 'app_document_crud_index', methods: ['GET'])]
    public function index(
        Request $request,
        DocumentRepository $documentRepository,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('search', '');
        $filtre = $request->query->get('filtre', 'tous');
        $tri    = $request->query->get('tri', 'dateAjout');
        $ordre  = $request->query->get('ordre', 'ASC');

        $query = $documentRepository->findByFiltersQuery($search, $filtre, $tri, $ordre);

        $documents = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );

        $allDocs = $documentRepository->findAll();
        $expires = $documentRepository->findExpired();

        return $this->render('document_crud/index.html.twig', [
            'documents' => $documents,
            'total'     => count($allDocs),
            'valides'   => count($allDocs) - count($expires),
            'expires'   => count($expires),
            'search'    => $search,
        ]);
    }

    // ===== NEW avec détection catégorie + Cloudinary =====
    #[Route('/new', name: 'app_document_crud_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        CategorieDetectorService $categorieDetector,
        CategorieDocumentRepository $categorieRepo,
        CloudinaryService $cloudinaryService  // ← Cloudinary injecté
    ): Response {
        $document = new Document();
        $form     = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // ← Détection automatique catégorie
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

            // ← Upload fichier vers Cloudinary
            $fichier = $form->get('fichier')->getData();
            if ($fichier) {
                try {
                    // ← Upload vers Cloudinary → retourne URL
                    $url = $cloudinaryService->upload(
                        $fichier->getRealPath(),
                        $fichier->getClientOriginalName()
                    );
                    $document->setCheminFichier($url);
                } catch (\Exception $e) {
                    // ← Si Cloudinary échoue → upload local en fallback
                    $nomFichier = uniqid() . '.' . $fichier->guessExtension();
                    $fichier->move($this->getParameter('uploads_directory'), $nomFichier);
                    $document->setCheminFichier($nomFichier);
                }
            } else {
                $document->setCheminFichier('aucun_fichier');
            }

            $entityManager->persist($document);
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
        $total        = count($documentRepository->findAll());
        $expires      = count($documentRepository->findExpired());
        $valides      = $total - $expires;
        $parCategorie = $documentRepository->countByCategorie();

        return $this->render('document_crud/stats.html.twig', [
            'total'        => $total,
            'expires'      => $expires,
            'valides'      => $valides,
            'parCategorie' => $parCategorie,
        ]);
    }

    // ===== EXPORT PDF =====
    #[Route('/export/pdf', name: 'app_document_export_pdf', methods: ['GET'])]
    public function exportPdf(DocumentRepository $documentRepository): Response
    {
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

        $filename = 'documents_' . date('Y-m-d') . '.pdf';

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
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
        ConseilsService $conseilsService
    ): Response {
        $document = $documentRepository->find($idDocument);
        if (!$document) {
            throw $this->createNotFoundException('Document non trouvé');
        }

        $conseils = $conseilsService->genererConseils(
            $document->getNomDocument(),
            $document->getCategorie()?->getLibelle() ?? 'Document',
            $document->getDateExpiration()?->format('d/m/Y')
        );

        return $this->render('document_crud/show.html.twig', [
            'document' => $document,
            'conseils' => $conseils,
        ]);
    }

    // ===== EDIT avec Cloudinary =====
    #[Route('/{idDocument}/edit', name: 'app_document_crud_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        int $idDocument,
        DocumentRepository $documentRepository,
        EntityManagerInterface $entityManager,
        CloudinaryService $cloudinaryService  // ← Cloudinary injecté
    ): Response {
        $document = $documentRepository->find($idDocument);
        if (!$document) {
            throw $this->createNotFoundException('Document non trouvé');
        }

        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

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

            // ← Upload nouveau fichier vers Cloudinary si fourni
            $fichier = $form->get('fichier')->getData();
            if ($fichier) {
                try {
                    // ← Upload vers Cloudinary → retourne URL
                    $url = $cloudinaryService->upload(
                        $fichier->getRealPath(),
                        $fichier->getClientOriginalName()
                    );
                    $document->setCheminFichier($url);
                } catch (\Exception $e) {
                    // ← Si Cloudinary échoue → upload local en fallback
                    $nomFichier = uniqid() . '.' . $fichier->guessExtension();
                    $fichier->move($this->getParameter('uploads_directory'), $nomFichier);
                    $document->setCheminFichier($nomFichier);
                }
            }
            // ← Si pas de nouveau fichier → garde l'ancien

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
}
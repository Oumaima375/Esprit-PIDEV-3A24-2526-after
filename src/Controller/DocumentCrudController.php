<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\CategorieDocument;
use App\Form\DocumentType;
use App\Repository\DocumentRepository;
use App\Repository\CategorieDocumentRepository;
use App\Service\GeminiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
 use Dompdf\Options;


#[Route('/document/crud')]
final class DocumentCrudController extends AbstractController
{
    // ===== INDEX =====
    #[Route(name: 'app_document_crud_index', methods: ['GET'])]
    public function index(
        Request $request,
        DocumentRepository $documentRepository
    ): Response {
        $search = $request->query->get('search', '');
        $filtre = $request->query->get('filtre', 'tous');
        $tri = $request->query->get('tri', 'dateAjout');
        $ordre = $request->query->get('ordre', 'DESC');

        $documents = $documentRepository->findByFilters($search, $filtre, $tri, $ordre);

        $total = count($documentRepository->findAll());
        $expires = count($documentRepository->findExpired());
        $valides = $total - $expires;

        return $this->render('document_crud/index.html.twig', [
            'documents' => $documents,
            'search' => $search,
            'filtre' => $filtre,
            'tri' => $tri,
            'ordre' => $ordre,
            'total' => $total,
            'expires' => $expires,
            'valides' => $valides,
        ]);
    }

    // ===== NEW avec Gemini =====
    #[Route('/new', name: 'app_document_crud_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Validation dateExpiration > dateAjout
            $dateAjout      = $document->getDateAjout();
            $dateExpiration = $document->getDateExpiration();

            if ($dateExpiration && $dateAjout && $dateExpiration <= $dateAjout) {
                $form->get('dateExpiration')->addError(
                    new \Symfony\Component\Form\FormError(
                        "La date d'expiration doit être après la date d'ajout."
                    )
                );
                return $this->render('document_crud/new.html.twig', [
                    'document' => $document,
                    'form'     => $form,
                ]);
            }

            // Gestion fichier — obligatoire en base donc on vérifie
            $fichier = $form->get('fichier')->getData();
            if ($fichier) {
                $nomFichier = uniqid() . '.' . $fichier->guessExtension();
                $fichier->move($this->getParameter('uploads_directory'), $nomFichier);
                $document->setCheminFichier($nomFichier);
            } else {
                // ← IMPORTANT : valeur par défaut si aucun fichier uploadé
                $document->setCheminFichier('aucun_fichier');
            }

            $entityManager->persist($document);
            $entityManager->flush();

            $this->addFlash('success', '✅ Document ajouté avec succès !');
            return $this->redirectToRoute('app_document_crud_index');
        }

        return $this->render('document_crud/new.html.twig', [
            'document' => $document,
            'form'     => $form,
        ]);
    }

    // ===== STATS (avant show !) =====
    #[Route('/stats', name: 'app_document_stats', methods: ['GET'])]
    public function stats(
        DocumentRepository $documentRepository,
        CategorieDocumentRepository $categorieRepo
    ): Response {
        $total = count($documentRepository->findAll());
        $expires = count($documentRepository->findExpired());
        $valides = $total - $expires;
        $parCategorie = $documentRepository->countByCategorie();

        return $this->render('document_crud/stats.html.twig', [
            'total' => $total,
            'expires' => $expires,
            'valides' => $valides,
            'parCategorie' => $parCategorie,
        ]);
    }

    // ===== EXPORT PDF (avant show !) =====
   // src/Controller/DocumentCrudController.php


    #[Route('/export/pdf', name: 'app_document_export_pdf', methods: ['GET'])]
    public function exportPdf(DocumentRepository $documentRepository): Response
    {
        $documents = $documentRepository->findAll();

        $html = $this->renderView('document_crud/pdf.html.twig', [
            'documents' => $documents,
        ]);

        // Configuration DomPDF
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'documents_' . date('Y-m-d') . '.pdf';

        // Force le téléchargement
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
    // ===== SHOW =====
    #[Route('/{idDocument}', name: 'app_document_crud_show', methods: ['GET'])]
    public function show(
        int $idDocument,
        DocumentRepository $documentRepository
    ): Response {
        $document = $documentRepository->find($idDocument);
        if (!$document) {
            throw $this->createNotFoundException('Document non trouvé');
        }
        return $this->render('document_crud/show.html.twig', [
            'document' => $document,
        ]);
    }

    // ===== EDIT =====
    #[Route('/{idDocument}/edit', name: 'app_document_crud_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        int $idDocument,
        DocumentRepository $documentRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $document = $documentRepository->find($idDocument);
        if (!$document) {
            throw $this->createNotFoundException('Document non trouvé');
        }

        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Validation dateExpiration > dateAjout
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

            // Gestion fichier — garder l'ancien si aucun nouveau uploadé
            $fichier = $form->get('fichier')->getData();
            if ($fichier) {
                $nomFichier = uniqid() . '.' . $fichier->guessExtension();
                $fichier->move($this->getParameter('uploads_directory'), $nomFichier);
                $document->setCheminFichier($nomFichier);
            }
            // Si pas de nouveau fichier → on garde l'ancien cheminFichier déjà en base

            $entityManager->flush();

            $this->addFlash('success', '✅ Document modifié avec succès !');
            return $this->redirectToRoute('app_document_crud_index');
        }

        return $this->render('document_crud/edit.html.twig', [
            'document' => $document,
            'form'     => $form,
        ]);
    }

    // ===== SUPPRIMER TOUS LES EXPIRES =====
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

    // ===== AJAX SEARCH =====
    #[Route('/search', name: 'app_document_search', methods: ['GET'])]
    public function search(
        Request $request,
        DocumentRepository $documentRepository
    ): Response {
        $search = $request->query->get('search', '');
        $filtre = $request->query->get('filtre', 'tous');
        $tri = $request->query->get('tri', 'dateAjout');

        $documents = $documentRepository->findByFilters($search, $filtre, $tri, 'DESC');

        $html = $this->renderView('document_crud/_cards.html.twig', [
            'documents' => $documents,
        ]);

        return $this->json([
            'html' => $html,
            'count' => count($documents),
        ]);
    }


    // ===== DELETE =====
  // src/Controller/DocumentCrudController.php

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

        // ← Vérifier si la requête vient du dashboard
        $referer = $request->headers->get('referer');
        if ($referer && str_contains($referer, '/admin/dashboard')) {
            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->redirectToRoute('app_document_crud_index');
    }
}
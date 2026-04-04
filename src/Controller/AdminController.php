<?php

namespace App\Controller;

use App\Entity\DocumentArchive;
use App\Repository\DocumentRepository;
use App\Repository\CategorieDocumentRepository;
use App\Repository\DocumentArchiveRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    // ===== DASHBOARD =====
#[Route('/dashboard', name: 'app_admin_dashboard')]
public function dashboard(
    DocumentRepository $documentRepository,
    CategorieDocumentRepository $categorieRepository,
    DocumentArchiveRepository $archiveRepository
): Response {
    $total = count($documentRepository->findAll());
    $documentsExpires = $documentRepository->findExpired();
    $expires = count($documentsExpires);
    $valides = $total - $expires;
    $parCategorie = $documentRepository->countByCategorie();
    $uploadsParMois = $documentRepository->getMonthlyUploads();
    $categories = $categorieRepository->findAll();
    $totalCategories = count($categories);
    $expiresSoon = count($documentRepository->findExpiringSoon());

    return $this->render('admin/dashboard.html.twig', [
        'total' => $total,
        'expires' => $expires,
        'valides' => $valides,
        'expiresSoon' => $expiresSoon,
        'parCategorie' => $parCategorie,
        'uploadsParMois' => $uploadsParMois,
        'documentsExpires' => $documentsExpires,
        'categories' => $categories,
        'totalCategories' => $totalCategories,
    ]);
}

    

    // ===== ARCHIVER + SUPPRIMER EXPIRES =====
    #[Route('/archiver-expires', name: 'app_admin_archiver_expires', methods: ['GET'])]
    public function archiverExpires(
        DocumentRepository $documentRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $expires = $documentRepository->findExpired();
        $count = 0;

        foreach ($expires as $doc) {
            // Créer archive
            $archive = new DocumentArchive();
            $archive->setIdDocumentOriginal($doc->getIdDocument());
            $archive->setNomDocument($doc->getNomDocument());
            $archive->setCheminFichier($doc->getCheminFichier());
            $archive->setDateAjout($doc->getDateAjout());
            $archive->setDateExpiration($doc->getDateExpiration());
            $archive->setCategorie($doc->getCategorie() ? $doc->getCategorie()->getLibelle() : 'N/A');
            $archive->setRaison('Expiré - Supprimé par admin');

            // Log notification (simulé - à brancher après intégration)
            error_log('📧 NOTIFICATION SIMULÉE - Document "' . $doc->getNomDocument() . '" supprimé par admin');

            $entityManager->persist($archive);
            $entityManager->remove($doc);
            $count++;
        }

        $entityManager->flush();
        $this->addFlash('success', '✅ ' . $count . ' documents archivés et supprimés ! Les voyageurs seront notifiés après intégration.');
        return $this->redirectToRoute('app_admin_dashboard');
    }

    // ===== VOIR ARCHIVES =====
    #[Route('/archives', name: 'app_admin_archives')]
    public function archives(DocumentArchiveRepository $archiveRepository): Response
    {
        return $this->render('admin/archives.html.twig', [
            'archives' => $archiveRepository->findBy([], ['dateArchivage' => 'DESC']),
        ]);
    }
}
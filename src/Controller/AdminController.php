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

use App\Service\MailjetService;

#[Route('/admin')]
class AdminController extends AbstractController
{
 // src/Controller/AdminController.php



    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function dashboard(
        DocumentRepository $documentRepository,
        CategorieDocumentRepository $categorieRepository
    ): Response {
        $today    = new \DateTime();
        $soon     = new \DateTime('+30 days');
        $allDocs  = $documentRepository->findAll();
        $expires  = $documentRepository->findExpired();
        $expiresSoon = $documentRepository->findExpiringSoon();

        return $this->render('admin/dashboard.html.twig', [
            'total'           => count($allDocs),
            'totalCategories' => count($categorieRepository->findAll()),
            'expiresSoon'     => count($expiresSoon),
            'expires'         => count($expires),
            'documentsExpires'=> $expires,
            'allDocuments'    => $allDocs,          // ← NOUVEAU
            'categories'      => $categorieRepository->findAll(),
            'parCategorie'    => $documentRepository->countByCategorie(),
            'uploadsParMois'  => $documentRepository->getMonthlyUploads(),
        ]);
    }


    

    // ===== ARCHIVER + SUPPRIMER EXPIRES =====

    #[Route('/archiver-expires', name: 'app_admin_archiver_expires', methods: ['GET'])]
    public function archiverExpires(
        DocumentRepository $documentRepository,
        EntityManagerInterface $entityManager,
        MailjetService $mailjetService
    ): Response {
        $expires = $documentRepository->findExpired();
        $count   = 0;

        foreach ($expires as $doc) {
            // ← Envoyer notification email
            $mailjetService->envoyerNotificationExpiration(
                'voyageur@example.com', // remplacer par $doc->getUser()->getEmail() après merge
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
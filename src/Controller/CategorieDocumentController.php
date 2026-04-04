<?php

namespace App\Controller;

use App\Entity\CategorieDocument;
use App\Form\CategorieDocumentType;
use App\Repository\CategorieDocumentRepository;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/categorie/document')]
final class CategorieDocumentController extends AbstractController
{
    #[Route(name: 'app_categorie_document_index', methods: ['GET'])]
    public function index(CategorieDocumentRepository $categorieDocumentRepository): Response
    {
        return $this->render('categorie_document/index.html.twig', [
            'categorie_documents' => $categorieDocumentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_categorie_document_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorieDocument = new CategorieDocument();
        $form = $this->createForm(CategorieDocumentType::class, $categorieDocument);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorieDocument);
            $entityManager->flush();

            return $this->redirectToRoute('app_categorie_document_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categorie_document/new.html.twig', [
            'categorie_document' => $categorieDocument,
            'form' => $form,
        ]);
    }

    #[Route('/{idCategorie}', name: 'app_categorie_document_show', methods: ['GET'])]
    public function show(
        int $idCategorie,
        CategorieDocumentRepository $categorieRepository,
        DocumentRepository $documentRepository
    ): Response {
        $categorie = $categorieRepository->find($idCategorie);
        if (!$categorie) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        $documents = $documentRepository->findBy(['categorie' => $categorie]);

        return $this->render('categorie_document/show.html.twig', [
            'categorie_document' => $categorie,
            'documents' => $documents,
        ]);
    }

    #[Route('/{idCategorie}/edit', name: 'app_categorie_document_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CategorieDocument $categorieDocument, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategorieDocumentType::class, $categorieDocument);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_categorie_document_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categorie_document/edit.html.twig', [
            'categorie_document' => $categorieDocument,
            'form' => $form,
        ]);
    }

    #[Route('/{idCategorie}', name: 'app_categorie_document_delete', methods: ['POST'])]
    public function delete(Request $request, CategorieDocument $categorieDocument, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorieDocument->getIdCategorie(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($categorieDocument);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_categorie_document_index', [], Response::HTTP_SEE_OTHER);
    }
}

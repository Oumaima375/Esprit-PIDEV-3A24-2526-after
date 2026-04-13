<?php
// src/Controller/CategorieDocumentController.php

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
    public function index(
        CategorieDocumentRepository $categorieDocumentRepository,
        DocumentRepository $documentRepository
    ): Response {
        $categories    = $categorieDocumentRepository->findAll();
        $totalDocuments = count($documentRepository->findAll());

        // Compteur par catégorie
        $compteurs = [];
        foreach ($categories as $cat) {
            $compteurs[$cat->getIdCategorie()] = count(
                $documentRepository->findBy(['categorie' => $cat])
            );
        }

        return $this->render('categorie_document/index.html.twig', [
            'categorie_documents' => $categories,
            'compteurs'           => $compteurs,
            'totalDocuments'      => $totalDocuments,
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
            $this->addFlash('success', '✅ Catégorie ajoutée avec succès !');
            // ← Reste dans le dashboard
            return $this->redirectToRoute('app_admin_dashboard');
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
            'documents'          => $documents,
        ]);
    }

    #[Route('/{idCategorie}/edit', name: 'app_categorie_document_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        int $idCategorie,
        CategorieDocumentRepository $categorieRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $categorieDocument = $categorieRepository->find($idCategorie);
        if (!$categorieDocument) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        $form = $this->createForm(CategorieDocumentType::class, $categorieDocument);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', '✅ Catégorie modifiée avec succès !');
            // ← Reste dans le dashboard
            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->render('categorie_document/edit.html.twig', [
            'categorie_document' => $categorieDocument,
            'form'               => $form,
        ]);
    }

    #[Route('/{idCategorie}', name: 'app_categorie_document_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        int $idCategorie,
        CategorieDocumentRepository $categorieRepository,
        DocumentRepository $documentRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $categorieDocument = $categorieRepository->find($idCategorie);

        if ($categorieDocument && $this->isCsrfTokenValid(
            'delete' . $idCategorie,
            $request->getPayload()->getString('_token')
        )) {
            // Vérifier si des documents utilisent cette catégorie
            $documentsLies = $documentRepository->findBy(['categorie' => $categorieDocument]);

            if (count($documentsLies) > 0) {
                $this->addFlash('error', '⚠️ Impossible de supprimer "' . $categorieDocument->getLibelle() . '" : ' . count($documentsLies) . ' document(s) utilisent cette catégorie.');
                return $this->redirectToRoute('app_admin_dashboard');
            }

            $entityManager->remove($categorieDocument);
            $entityManager->flush();
            $this->addFlash('success', '🗑 Catégorie supprimée !');
        }

        return $this->redirectToRoute('app_admin_dashboard');
    }
}
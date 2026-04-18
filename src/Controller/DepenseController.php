<?php

namespace App\Controller;

use App\Entity\Depense;
use App\Form\DepenseType;
use App\Repository\DepenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/depense')]
class DepenseController extends AbstractController
{
    #[Route('/', name: 'depense_index', methods: ['GET'])]
    public function index(Request $request, DepenseRepository $depenseRepository, \App\Repository\CategorieRepository $categorieRepository): Response
    {
        $titre = $request->query->get('titre');
        $categorieId = $request->query->get('categorie');

        $qb = $depenseRepository->createQueryBuilder('d')
            ->leftJoin('d.id_categorie', 'c')
            ->addSelect('c');

        if ($titre) {
            $qb->andWhere('d.titre LIKE :titre')->setParameter('titre', '%' . $titre . '%');
        }
        if ($categorieId) {
            $qb->andWhere('d.id_categorie = :cat')->setParameter('cat', $categorieId);
        }
        $qb->orderBy('d.date_depense', 'DESC');

        $depenses = $qb->getQuery()->getResult();
        $categories = $categorieRepository->findAll();

        if ($request->isXmlHttpRequest()) {
            return $this->render('depense/_list.html.twig', [
                'depenses' => $depenses,
            ]);
        }

        return $this->render('depense/index.html.twig', [
            'depenses' => $depenses,
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'depense_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $depense = new Depense();
        $form = $this->createForm(DepenseType::class, $depense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($depense);
            $entityManager->flush();
            $this->addFlash('success', 'Dépense ajoutée.');

            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => true]);
            }
            return $this->redirectToRoute('depense_index');
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('depense/_form.html.twig', [
                'depense' => $depense,
                'form' => $form->createView(),
            ]);
        }

        return $this->render('depense/new.html.twig', [
            'depense' => $depense,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'depense_show', methods: ['GET'])]
    public function show(Depense $depense): Response
    {
        return $this->render('depense/show.html.twig', [
            'depense' => $depense,
        ]);
    }

    #[Route('/{id}/edit', name: 'depense_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Depense $depense, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DepenseType::class, $depense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Dépense modifiée.');

            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => true]);
            }
            return $this->redirectToRoute('depense_index');
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('depense/_form.html.twig', [
                'depense' => $depense,
                'form'    => $form->createView(),
            ]);
        }

        return $this->render('depense/edit.html.twig', [
            'depense' => $depense,
            'form'    => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'depense_delete', methods: ['POST'])]
    public function delete(Request $request, Depense $depense, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$depense->getIdDep(), $request->request->get('_token'))) {
            $entityManager->remove($depense);
            $entityManager->flush();
            $this->addFlash('success', 'Dépense supprimée.');
        }
        return $this->redirectToRoute('depense_index');
    }

    #[Route('/api/localisation', name: 'api_localisation', methods: ['GET'])]
    public function localisation(): JsonResponse
    {
        $timeout = 3;
        $ctx = stream_context_create(['http' => ['timeout' => $timeout]]);

        $data = @file_get_contents('https://ipapi.co/json/', false, $ctx);
        if ($data !== false) {
            $pos = json_decode($data, true);
            if ($pos && isset($pos['latitude'], $pos['longitude'])) {
                return $this->json([
                    'latitude'  => $pos['latitude'],
                    'longitude' => $pos['longitude'],
                    'city'      => $pos['city'] ?? '',
                    'country'   => $pos['country_name'] ?? ''
                ]);
            }
        }

        $data = @file_get_contents('http://ip-api.com/json/', false, $ctx);
        if ($data !== false) {
            $pos = json_decode($data, true);
            if ($pos && $pos['status'] === 'success') {
                return $this->json([
                    'latitude'  => $pos['lat'],
                    'longitude' => $pos['lon'],
                    'city'      => $pos['city'],
                    'country'   => $pos['country']
                ]);
            }
        }

        return $this->json(['error' => 'Localisation impossible'], 500);
    }
}
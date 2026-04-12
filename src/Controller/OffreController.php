<?php
namespace App\Controller;

use App\Entity\Offre;
use App\Entity\Service;
use App\Form\OffreType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/offre')]
class OffreController extends AbstractController
{
    #[Route('/', name: 'app_offre_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $search = $request->query->get('search', '');
        $serviceId = $request->query->get('service_id', '');

        $qb = $em->getRepository(Offre::class)->createQueryBuilder('o');

        if ($search) {
            $qb->andWhere('o.titre LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($serviceId) {
            $qb->andWhere('o.service = :service')
               ->setParameter('service', $serviceId);
        }

        $offres = $qb->getQuery()->getResult();
        $services = $em->getRepository(Service::class)->findAll();

        return $this->render('offre/index.html.twig', [
            'offres' => $offres,
            'services' => $services,
        ]);
    }

    #[Route('/new', name: 'app_offre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $offre = new Offre();
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($offre);
            $em->flush();
            $this->addFlash('success', 'Offre créée avec succès !');
            return $this->redirectToRoute('app_offre_index');
        }

        return $this->render('offre/new.html.twig', [
            'offre' => $offre,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_offre_show', methods: ['GET'])]
    public function show(Offre $offre): Response
    {
        return $this->render('offre/show.html.twig', [
            'offre' => $offre,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_offre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Offre $offre, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Offre modifiée avec succès !');
            return $this->redirectToRoute('app_offre_index');
        }

        return $this->render('offre/edit.html.twig', [
            'offre' => $offre,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_offre_delete', methods: ['POST'])]
    public function delete(Request $request, Offre $offre, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$offre->getId(), $request->request->get('_token'))) {
            $em->remove($offre);
            $em->flush();
            $this->addFlash('success', 'Offre supprimée !');
        }
        return $this->redirectToRoute('app_offre_index');
    }
}
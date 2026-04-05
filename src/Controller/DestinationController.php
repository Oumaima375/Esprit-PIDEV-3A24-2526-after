<?php

namespace App\Controller;

use App\Entity\Destination;
use App\Form\DestinationType;
use App\Repository\DestinationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DestinationController extends AbstractController
{
    #[Route('/destination', name: 'app_destination')]
    public function index(DestinationRepository $destinationRepository): Response
    {
        $destinations = $destinationRepository->findAll();
        return $this->render('destination/index.html.twig', [
            'destinations' => $destinations,
        ]);
    }

    #[Route('/destination/new', name: 'app_destination_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $destination = new Destination();
        $form = $this->createForm(DestinationType::class, $destination);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($destination);
            $em->flush();
            $this->addFlash('success', 'Destination ajoutée !');
            return $this->redirectToRoute('app_destination');
        }

        return $this->render('destination/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/destination/edit/{id}', name: 'app_destination_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $em, DestinationRepository $destinationRepository): Response
    {
        $destination = $destinationRepository->findOneBy(['id_destination' => $id]);
        if (!$destination) throw $this->createNotFoundException('Destination non trouvée');

        $form = $this->createForm(DestinationType::class, $destination);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Destination modifiée !');
            return $this->redirectToRoute('app_destination');
        }

        return $this->render('destination/edit.html.twig', [
            'form' => $form->createView(),
            'destination' => $destination,
        ]);
    }

    #[Route('/destination/delete/{id}', name: 'app_destination_delete')]
    public function delete(int $id, EntityManagerInterface $em, DestinationRepository $destinationRepository): Response
    {
        $destination = $destinationRepository->findOneBy(['id_destination' => $id]);
        if ($destination) {
            $em->remove($destination);
            $em->flush();
            $this->addFlash('success', 'Destination supprimée !');
        }
        return $this->redirectToRoute('app_destination');
    }
}
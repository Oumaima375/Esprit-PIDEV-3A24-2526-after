<?php
namespace App\Controller\Admin;
use App\Repository\DestinationRepository;
use App\Form\DestinationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
class DestinationAdminController extends AbstractController
{
    #[Route('/admin/destination', name: 'admin_destination_index')]
    public function index(DestinationRepository $repo): Response
    {
        return $this->render('admin/destinations/index.html.twig', [
            'destinations' => $repo->findAll(),
        ]);
    }
    #[Route('/admin/destination/new', name: 'admin_destination_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $dest = new \App\Entity\Destination();
        $form = $this->createForm(DestinationType::class, $dest);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $fn = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir').'/public/img/', $fn);
                $dest->setImage('/img/'.$fn);
            }
            $em->persist($dest);
            $em->flush();
            $this->addFlash('success', 'Destination ajoutee !');
            return $this->redirectToRoute('admin_destination_index');
        }
        return $this->render('admin/destinations/new.html.twig', ['form' => $form->createView()]);
    }
    #[Route('/admin/destination/edit/{id}', name: 'admin_destination_edit')]
    public function edit(int $id, Request $req, EntityManagerInterface $em, DestinationRepository $repo): Response
    {
        $dest = $repo->findOneBy(['id_destination' => $id]);
        if (!$dest) throw $this->createNotFoundException();
        $form = $this->createForm(DestinationType::class, $dest);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $fn = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir').'/public/img/', $fn);
                $dest->setImage('/img/'.$fn);
            }
            $em->flush();
            $this->addFlash('success', 'Destination modifiee !');
            return $this->redirectToRoute('admin_destination_index');
        }
        return $this->render('admin/destinations/edit.html.twig', [
            'form' => $form->createView(), 'destination' => $dest
        ]);
    }
    #[Route('/admin/destination/delete/{id}', name: 'admin_destination_delete')]
    public function delete(int $id, EntityManagerInterface $em, DestinationRepository $repo): Response
    {
        $d = $repo->findOneBy(['id_destination' => $id]);
        if ($d) { $em->remove($d); $em->flush(); }
        $this->addFlash('success', 'Destination supprimee.');
        return $this->redirectToRoute('admin_destination_index');
    }
}
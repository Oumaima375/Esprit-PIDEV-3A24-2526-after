<?php
namespace App\Controller\Admin;
use App\Repository\VoyageRepository;
use App\Form\VoyageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
class VoyageAdminController extends AbstractController
{
    #[Route('/admin/voyage', name: 'admin_voyage_index')]
    public function index(VoyageRepository $repo): Response
    {
        return $this->render('admin/voyages/index.html.twig', [
            'voyages' => $repo->findBy([], ['id_voyage' => 'DESC']),
        ]);
    }
    #[Route('/admin/voyage/new', name: 'admin_voyage_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $voyage = new \App\Entity\Voyage();
        $form = $this->createForm(VoyageType::class, $voyage);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $fn = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir').'/public/img/', $fn);
                $voyage->setImage('/img/'.$fn);
            }
            $em->persist($voyage);
            $em->flush();
            $this->addFlash('success', 'Voyage ajoute !');
            return $this->redirectToRoute('admin_voyage_index');
        }
        return $this->render('admin/voyages/new.html.twig', ['form' => $form->createView()]);
    }
    #[Route('/admin/voyage/edit/{id}', name: 'admin_voyage_edit')]
    public function edit(int $id, Request $req, EntityManagerInterface $em, VoyageRepository $repo): Response
    {
        $voyage = $repo->findOneBy(['id_voyage' => $id]);
        if (!$voyage) throw $this->createNotFoundException();
        $form = $this->createForm(VoyageType::class, $voyage);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $fn = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir').'/public/img/', $fn);
                $voyage->setImage('/img/'.$fn);
            }
            $em->flush();
            $this->addFlash('success', 'Voyage modifie !');
            return $this->redirectToRoute('admin_voyage_index');
        }
        return $this->render('admin/voyages/edit.html.twig', [
            'form' => $form->createView(), 'voyage' => $voyage
        ]);
    }
    #[Route('/admin/voyage/delete/{id}', name: 'admin_voyage_delete')]
    public function delete(int $id, EntityManagerInterface $em, VoyageRepository $repo): Response
    {
        $v = $repo->findOneBy(['id_voyage' => $id]);
        if ($v) { $em->remove($v); $em->flush(); }
        $this->addFlash('success', 'Voyage supprime.');
        return $this->redirectToRoute('admin_voyage_index');
    }
}
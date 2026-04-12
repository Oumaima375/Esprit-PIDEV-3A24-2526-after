<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Activite;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AvisController extends AbstractController
{
    #[Route('/activite/{id}/avis', name: 'app_avis_liste')]
    public function liste(Activite $activite): Response
    {
        return $this->render('avis/liste.html.twig', [
            'activite' => $activite,
            'avis'     => $activite->getAvis(),
        ]);
    }

    #[Route('/activite/{id}/avis/new', name: 'app_avis_new', methods: ['POST'])]
    public function new(
        Activite $activite,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $avis = new Avis();
        $avis->setActivite($activite);
        $avis->setAuteur($user);
        $avis->setCommentaire($request->request->get('commentaire'));
        $avis->setNote((int) $request->request->get('note', 5));
        $avis->setCreatedAt(new \DateTime());

        $em->persist($avis);
        $em->flush();

        return $this->redirectToRoute('app_avis_liste', [
            'id' => $activite->getIdActivite()
        ]);
    }
}
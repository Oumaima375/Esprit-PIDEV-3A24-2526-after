<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Form\ActiviteType;
use App\Repository\ActiviteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Snappy\Pdf;

// QR CODE
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class ActiviteController extends AbstractController
{
    #[Route('/activite', name: 'app_activite')]
    public function index(ActiviteRepository $activiteRepository): Response
    {
        $activites = $activiteRepository->findAll();
        return $this->render('activite/index.html.twig', [
            'activites' => $activites,
        ]);
    }

    // ===================== RECHERCHE AJAX =====================
    #[Route('/activite/search', name: 'app_activite_search')]
    public function search(Request $request, NormalizerInterface $normalizer, ActiviteRepository $activiteRepository): JsonResponse
    {
        $searchValue = $request->get('searchValue');
        $activites = $activiteRepository->findActiviteByNom($searchValue);
        $jsonContent = $normalizer->normalize($activites, 'json', ['groups' => 'activites']);
        return new JsonResponse($jsonContent);
    }

    // ===================== TRI AJAX =====================
    #[Route('/activite/sort', name: 'app_activite_sort')]
    public function sort(Request $request, NormalizerInterface $normalizer, ActiviteRepository $activiteRepository): JsonResponse
    {
        $sortBy = $request->get('sortBy', 'nom');
        $activites = $activiteRepository->findActivitesSorted($sortBy);
        $jsonContent = $normalizer->normalize($activites, 'json', ['groups' => 'activites']);
        return new JsonResponse($jsonContent);
    }

    // ===================== ADD =====================
    #[Route('/activite/add', name: 'app_activite_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $activite = new Activite();
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($activite);
            $em->flush();
            return $this->redirectToRoute('app_activite');
        }

        return $this->render('activite/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ===================== EDIT =====================
    #[Route('/activite/edit/{id}', name: 'app_activite_edit')]
    public function edit(int $id, Request $request, ActiviteRepository $activiteRepository, EntityManagerInterface $em): Response
    {
        $activite = $activiteRepository->find($id);

        if (!$activite) {
            throw $this->createNotFoundException('Activité introuvable');
        }

        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_activite');
        }

        return $this->render('activite/edit.html.twig', [
            'form' => $form->createView(),
            'activite' => $activite,
        ]);
    }

    // ===================== DELETE =====================
    #[Route('/activite/delete/{id}', name: 'app_activite_delete')]
    public function delete(int $id, ActiviteRepository $activiteRepository, EntityManagerInterface $em): Response
    {
        $activite = $activiteRepository->find($id);

        if (!$activite) {
            throw $this->createNotFoundException('Activité introuvable');
        }

        $em->remove($activite);
        $em->flush();
        return $this->redirectToRoute('app_activite');
    }

   // ================= PDF =================
    #[Route('/activite/pdf/{id}', name: 'app_activite_pdf')]
    public function pdfActivite(
        int $id,
        ActiviteRepository $activiteRepository,
        Pdf $knpSnappyPdf
    ): Response {
        $activite = $activiteRepository->find($id);

        if (!$activite) {
            throw $this->createNotFoundException('Activité introuvable');
        }

        // Générer le HTML pour le PDF
        $html = $this->renderView('activite/pdf.html.twig', [
            'activite' => $activite
        ]);

        $options = [
            'enable-local-file-access' => true,
        ];

        return new Response(
            $knpSnappyPdf->getOutputFromHtml($html, $options),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="activite-'.$activite->getIdActivite().'.pdf"'
            ]
        );
    }
 


    // ===================== QR CODE =====================
    #[Route('/activite/qr', name: 'app_activite_qr')]
    public function qr(Request $request): Response
    {
        $host = $_ENV['LOCAL_IP'] ?? $request->getHost();
        $url = $request->getScheme() . '://' . $host . '/after-symfony/after-symfony/public/index.php/activite';

        $qrCode = new QrCode($url);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return new Response(
            $result->getString(),
            Response::HTTP_OK,
            ['Content-Type' => 'image/png']
        );
    }
}
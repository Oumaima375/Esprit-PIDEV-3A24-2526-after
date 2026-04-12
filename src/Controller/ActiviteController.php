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

        $imageFile = $form->get('image')->getData();

        if ($imageFile) {
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();

            $imageFile->move(
                $this->getParameter('kernel.project_dir') . '/public/uploads/activites',
                $newFilename
            );

            $activite->setImage($newFilename);
        }

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
    #[Route('/activite/{id}/plannings', name: 'app_activite_plannings')]
public function getPlannings(int $id, ActiviteRepository $activiteRepository): JsonResponse
{
    $activite = $activiteRepository->find($id);
    if (!$activite) {
        return $this->json(['error' => 'Activité introuvable'], 404);
    }

    $data = [];
    foreach ($activite->getPlannings() as $planning) {
        $reservations = $planning->getReservations();
        $estReserve = count($reservations) > 0;

        $data[] = [
            'id'          => $planning->getIdPlanning(),
            'date'        => $planning->getDateActivite()->format('Y-m-d'),
            'heure'       => $planning->getHeureDebut(),
            'duree'       => $planning->getDuree(),
            'disponible'  => !$estReserve,
            'reservations' => $reservations->count(),
        ];
    }

    return $this->json($data);
}
// ===================== DASHBOARD ADMIN =====================
#[Route('/admin/activites/dashboard', name: 'admin_activites_dashboard')]
public function dashboard(ActiviteRepository $activiteRepository): Response
{
    $activites = $activiteRepository->findAll();

    $totalActivites = count($activites);
    $categories = [];
    $prixParCategorie = [];
    $totalPrix = 0;
    $count = 0;

    foreach ($activites as $a) {
        $cat = $a->getCategorie() ?? 'Autre';
        $categories[$cat] = ($categories[$cat] ?? 0) + 1;
        if ($a->getPrix()) {
            $prixParCategorie[$cat] = ($prixParCategorie[$cat] ?? 0) + $a->getPrix();
            $totalPrix += $a->getPrix();
            $count++;
        }
    }

    $prixMoyen = $count > 0 ? round($totalPrix / $count) : 0;
    $dernieres = $activiteRepository->findBy([], ['id_activite' => 'DESC'], 5);

    $plannings = [];
    foreach ($activites as $a) {
        foreach ($a->getPlannings() as $p) {
            $plannings[] = $p;
        }
    }

    return $this->render('activite/dashboard.html.twig', [
        'activites'            => $activites,
        'plannings'            => $plannings,
        'totalActivites'       => $totalActivites,
        'totalCategories'      => count($categories),
        'prixMoyen'            => $prixMoyen,
        'categories'           => $categories,
        'prixParCategorie'     => $prixParCategorie,
        'prixParCategorieKeys' => array_keys($prixParCategorie),
        'prixParCategorieVals' => array_values($prixParCategorie),
        'dernieres'            => $dernieres,
    ]);
}
// ===================== BACKOFFICE ACTIVITE ADD =====================
#[Route('/admin/activite/add', name: 'admin_activite_add')]
public function adminActiviteAdd(Request $request, EntityManagerInterface $em): Response
{
    $activite = new Activite();
    $form = $this->createForm(ActiviteType::class, $activite);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move(
                $this->getParameter('kernel.project_dir') . '/public/uploads/activites',
                $newFilename
            );
            $activite->setImage($newFilename);
        }
        $em->persist($activite);
        $em->flush();
        $this->addFlash('success', 'Activité ajoutée avec succès !');
        return $this->redirectToRoute('admin_activites_dashboard');
    }

    return $this->render('activite/activite_add.html.twig', [
        'form' => $form->createView(),
    ]);
}

// ===================== BACKOFFICE ACTIVITE EDIT =====================
#[Route('/admin/activite/edit/{id}', name: 'admin_activite_edit')]
public function adminActiviteEdit(int $id, Request $request, ActiviteRepository $activiteRepository, EntityManagerInterface $em): Response
{
    $activite = $activiteRepository->find($id);

    if (!$activite) {
        throw $this->createNotFoundException('Activité introuvable');
    }

    $form = $this->createForm(ActiviteType::class, $activite);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move(
                $this->getParameter('kernel.project_dir') . '/public/uploads/activites',
                $newFilename
            );
            $activite->setImage($newFilename);
        }
        $em->flush();
        $this->addFlash('success', 'Activité modifiée avec succès !');
        return $this->redirectToRoute('admin_activites_dashboard');
    }

    return $this->render('activite/activite_edit.html.twig', [
        'form'     => $form->createView(),
        'activite' => $activite,
    ]);
}

// ===================== BACKOFFICE ACTIVITE DELETE =====================
#[Route('/admin/activite/delete/{id}', name: 'admin_activite_delete')]
public function adminActiviteDelete(int $id, ActiviteRepository $activiteRepository, EntityManagerInterface $em): Response
{
    $activite = $activiteRepository->find($id);

    if (!$activite) {
        throw $this->createNotFoundException('Activité introuvable');
    }

    $em->remove($activite);
    $em->flush();
    $this->addFlash('success', 'Activité supprimée avec succès !');
    return $this->redirectToRoute('admin_activites_dashboard');
}
// ===================== BACKOFFICE PLANNING ADD =====================
#[Route('/admin/planning/add', name: 'admin_planning_add')]
public function adminPlanningAdd(Request $request, EntityManagerInterface $em): Response
{
    $planning = new \App\Entity\Planning();
    $form = $this->createForm(\App\Form\PlanningType::class, $planning);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($planning);
        $em->flush();
        $this->addFlash('success', 'Planning ajouté avec succès !');
        return $this->redirectToRoute('admin_activites_dashboard');
    }

    return $this->render('activite/admin_planning_add.html.twig', [
        'form' => $form->createView(),
    ]);
}

// ===================== BACKOFFICE PLANNING EDIT =====================
#[Route('/admin/planning/edit/{id}', name: 'admin_planning_edit')]
public function adminPlanningEdit(int $id, Request $request, EntityManagerInterface $em): Response
{
    $planning = $em->getRepository(\App\Entity\Planning::class)->find($id);

    if (!$planning) {
        throw $this->createNotFoundException('Planning introuvable');
    }

    $form = $this->createForm(\App\Form\PlanningType::class, $planning);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();
        $this->addFlash('success', 'Planning modifié avec succès !');
        return $this->redirectToRoute('admin_activites_dashboard');
    }

    return $this->render('activite/admin_planning_edit.html.twig', [
        'form'     => $form->createView(),
        'planning' => $planning,
    ]);
}

// ===================== BACKOFFICE PLANNING DELETE =====================
#[Route('/admin/planning/delete/{id}', name: 'admin_planning_delete')]
public function adminPlanningDelete(int $id, EntityManagerInterface $em): Response
{
    $planning = $em->getRepository(\App\Entity\Planning::class)->find($id);

    if (!$planning) {
        throw $this->createNotFoundException('Planning introuvable');
    }

    $em->remove($planning);
    $em->flush();
    $this->addFlash('success', 'Planning supprimé avec succès !');
    return $this->redirectToRoute('admin_activites_dashboard');
}
}
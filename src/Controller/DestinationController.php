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
<<<<<<< Updated upstream
<<<<<<< Updated upstream
=======
=======
>>>>>>> Stashed changes
    #[Route('/destination/{id}', name: 'app_destination_show', requirements: ['id' => '\d+'])]
public function show(int $id, DestinationRepository $repo): Response
{
 $dest = $repo->findOneBy(['id_destination' => $id]);
 if (!$dest) throw $this->createNotFoundException('Destination non trouvee');
 $attractions = [];
 try {
 $ctx = stream_context_create(['http'=>['header'=>'User-Agent: AfterTravel/1.0']]);
 $geo = json_decode(file_get_contents(
 'https://nominatim.openstreetmap.org/search?q='.urlencode($dest->getVille()).'&format=json&limit=1',
 false, $ctx
 ), true);
 if (!empty($geo)) {
 $lat=$geo[0]['lat']; $lon=$geo[0]['lon'];
 $q='[out:json][timeout:10];node["tourism"](around:5000,'.$lat.','.$lon.');out 5;';
 $data = json_decode(file_get_contents(
 'https://overpass-api.de/api/interpreter?data='.urlencode($q)
 ), true);
 foreach ($data['elements']??[] as $el) {
 $name = $el['tags']['name'] ?? null;
 $type = $el['tags']['tourism'] ?? '';
 if ($name) $attractions[] = ['name'=>$name,'type'=>$type];
 }
 }
 } catch (\Exception $e) {}
 return $this->render('destination/show.html.twig', [
 'destination' => $dest, 'attractions' => $attractions,
 ]);
}

<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
}
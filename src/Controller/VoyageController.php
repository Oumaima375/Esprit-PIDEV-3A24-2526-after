<?php

namespace App\Controller;

use App\Entity\Voyage;
use App\Repository\VoyageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
<<<<<<< Updated upstream

class VoyageController extends AbstractController
{
    #[Route('/voyage', name: 'app_voyage')]
public function index(VoyageRepository $voyageRepository, Request $request): Response
{
    $search = $request->query->get('search');
    $prixMin = $request->query->get('prix_min');
    $prixMax = $request->query->get('prix_max');
    $dateDebut = $request->query->get('date_debut');
    $placesMin = $request->query->get('places_min');

    $voyages = $voyageRepository->findWithFilters($search, $prixMin, $prixMax, $dateDebut, $placesMin);

    return $this->render('voyage/index.html.twig', [
        'voyages' => $voyages,
    ]);
}
=======
use App\Form\VoyageType;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\PdfService;
use Knp\Component\Pager\PaginatorInterface;

class VoyageController extends AbstractController
{
    #[Route('/voyage/{id}/pdf', name: 'app_voyage_pdf', requirements: ['id' => '\d+'])]
public function exportPdf(int $id, VoyageRepository $repo, PdfService $pdfService): Response
{
 $voyage = $repo->findOneBy(['id_voyage' => $id]);
 if (!$voyage) throw $this->createNotFoundException('Voyage non trouve');
 $dest = $voyage->getIdDestination();
 $data = [
 'titre' => $voyage->getTitre(),
 'description' => $voyage->getDescription(),
 'date_debut' => $voyage->getDate_debut()?->format('d/m/Y') ?? 'N/A',
 'date_fin' => $voyage->getDate_fin()?->format('d/m/Y') ?? 'N/A',
 'prix' => $voyage->getPrix(),
 'nb_places' => $voyage->getNb_places(),
 'destination' => $dest ? $dest->getPays() . ' - ' . $dest->getVille() : 'N/A',
 ];
 $pdfContent = $pdfService->generateVoyagePdf($data);
 return new Response($pdfContent, 200, [
 'Content-Type' => 'application/pdf',
 'Content-Disposition' => 'attachment; filename="voyage.pdf"',
 ]);
}

   #[Route('/voyage', name: 'app_voyage')]
public function index(VoyageRepository $repo, Request $request, PaginatorInterface $paginator): Response
{
 $search = $request->query->get('search');
 $prixMin = $request->query->get('prix_min');
 $prixMax = $request->query->get('prix_max');
 $placesMin = $request->query->get('places_min');
 $query = $repo->findWithFiltersQuery($search, $prixMin, $prixMax, $placesMin);
 $voyages = $paginator->paginate($query, $request->query->getInt('page', 1), 3);
 return $this->render('voyage/index.html.twig', ['voyages' => $voyages]);
}

>>>>>>> Stashed changes

    #[Route('/voyage/favoris', name: 'app_voyage_favoris')]
    public function favoris(VoyageRepository $voyageRepository, Request $request): Response
    {
        $favorisIds = $request->getSession()->get('favoris', []);
        $voyages = empty($favorisIds) ? [] : $voyageRepository->findBy(['id' => $favorisIds]);
        return $this->render('voyage/favoris.html.twig', [
            'voyages' => $voyages,
        ]);
    }

    #[Route('/voyage/recommandations', name: 'app_voyage_recommandations')]
public function recommandations(VoyageRepository $voyageRepository, Request $request): Response
{
    $search = $request->query->get('search');
    $prixMin = $request->query->get('prix_min');
    $prixMax = $request->query->get('prix_max');
    $dateDebut = $request->query->get('date_debut');
    $placesMin = $request->query->get('places_min');

    $voyages = $voyageRepository->findWithFilters($search, $prixMin, $prixMax, $dateDebut, $placesMin);

    return $this->render('voyage/recommandations.html.twig', [
        'voyages' => $voyages,
    ]);
}
#[Route('/voyage/toggle-favori/{id}', name: 'app_voyage_toggle_favori')]
public function toggleFavori(int $id, Request $request): Response
{
    $session = $request->getSession();
    $favoris = $session->get('favoris', []);

    if (in_array($id, $favoris)) {
        $favoris = array_filter($favoris, fn($f) => $f !== $id);
    } else {
        $favoris[] = $id;
    }

    $session->set('favoris', array_values($favoris));
    return $this->redirectToRoute('app_voyage');
}

<<<<<<< Updated upstream
#[Route('/voyage/new', name: 'app_voyage_new')]
public function new(): Response
{
    return $this->render('voyage/new.html.twig');
=======
        if (in_array($id, $favoris)) {
            $favoris = array_filter($favoris, fn($f) => $f !== $id);
        } else {
            $favoris[] = $id;
        }

        $session->set('favoris', array_values($favoris));
        return $this->redirectToRoute('app_voyage');
    }

    #[Route('/voyage/new', name: 'app_voyage_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $voyage = new \App\Entity\Voyage();
        $form = $this->createForm(VoyageType::class, $voyage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $imageFile = $form->get('imageFile')->getData();

                if ($imageFile) {
                    $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/img/',
                        $newFilename
                    );
                    $voyage->setImage('/img/' . $newFilename);
                } elseif (!$voyage->getImage()) {
                    $voyage->setImage('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800');
                }

                $em->persist($voyage);
                $em->flush();

                $this->addFlash('success', 'Voyage ajouté avec succès !');
                return $this->redirectToRoute('app_voyage');

            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
            }
        }

        return $this->render('voyage/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/voyage/edit/{id}', name: 'app_voyage_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $em, VoyageRepository $voyageRepository): Response
    {
        $voyage = $voyageRepository->findOneBy(['id_voyage' => $id]);
        if (!$voyage) {
            throw $this->createNotFoundException('Voyage non trouvé');
        }

        $form = $this->createForm(VoyageType::class, $voyage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            $imageUrl = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/img/',
                    $newFilename
                );
                $voyage->setImage('/img/' . $newFilename);
            } elseif ($imageUrl) {
                $voyage->setImage($imageUrl);
            }

            $em->flush();
            $this->addFlash('success', 'Voyage modifié avec succès !');
            return $this->redirectToRoute('app_voyage');
        }

        return $this->render('voyage/edit.html.twig', [
            'form' => $form->createView(),
            'voyage' => $voyage,
        ]);
    }

    #[Route('/voyage/delete/{id}', name: 'app_voyage_delete')]
    public function delete(int $id, EntityManagerInterface $em, VoyageRepository $voyageRepository): Response
    {
        $voyage = $voyageRepository->findOneBy(['id_voyage' => $id]);
        if ($voyage) {
            $em->remove($voyage);
            $em->flush();
            $this->addFlash('success', 'Voyage supprimé avec succès !');
        }
        return $this->redirectToRoute('app_voyage');
    }

   #[Route('/voyage/{id}', name: 'app_voyage_show', requirements: ['id' => '\d+'])]
public function show(int $id, VoyageRepository $repo): Response
{
 $voyage = $repo->findOneBy(['id_voyage' => $id]);
 if (!$voyage) throw $this->createNotFoundException('Voyage introuvable.');
 $meteo = null;
 $timezone = null;
 $dest = $voyage->getIdDestination();
 if ($dest) {
 $ville = $dest->getVille();
 // METEO
 try {
 $ctx = stream_context_create(['http'=>['header'=>'User-Agent: AfterTravel/1.0']]);
 $geo = json_decode(file_get_contents(
    'https://nominatim.openstreetmap.org/search?q='.urlencode($ville).'&format=json&limit=1', false, $ctx
), true);
 if (!empty($geo)) {
 $lat = $geo[0]['lat']; $lon = $geo[0]['lon'];
 $w = json_decode(file_get_contents(
 "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&current_weather=true"
 ), true)['current_weather'];
 $code = $w['weathercode'];
 $desc = match(true) {
 $code===0 => 'Ciel degage', $code<=2 => 'Partiellement nuageux',
 $code===3 => 'Couvert', $code<=49 => 'Brouillard',
 $code<=59 => 'Bruine', $code<=69 => 'Pluie',
 $code<=79 => 'Neige', $code<=84 => 'Averses', default => 'Orage'
 };
 $meteo = [
 'temperature' => $w['temperature'], 'windspeed' => $w['windspeed'],
 'description' => $desc, 'ville' => $ville,
 'conseil' => $w['temperature']>35 ? 'Forte chaleur — restez hydrate' :
 ($code>=61 ? 'Pluie prevue — prenez un impermeable' : 'Bon temps pour voyager !'),
 ];
 }
 } catch (\Exception $e) {}
 // TIMEZONE
 $pays = strtolower($dest->getPays());
 $tzMap = ['france'=>'Europe/Paris','espagne'=>'Europe/Madrid','spain'=>'Europe/Madrid',
 'italie'=>'Europe/Rome','italy'=>'Europe/Rome','allemagne'=>'Europe/Berlin',
 'tunisie'=>'Africa/Tunis','tunisia'=>'Africa/Tunis','maroc'=>'Africa/Casablanca',
 'egypte'=>'Africa/Cairo','japon'=>'Asia/Tokyo','japan'=>'Asia/Tokyo',
 'emirats'=>'Asia/Dubai','usa'=>'America/New_York','royaume-uni'=>'Europe/London'];
 $tz = 'UTC';
 foreach ($tzMap as $k=>$z) { if (str_contains($pays,$k)) { $tz=$z; break; } }
 try {
 $zone = new \DateTimeZone($tz);
 $now = new \DateTime('now', $zone);
 $tunis = new \DateTimeZone('Africa/Tunis');
 $diff = ($zone->getOffset($now) - $tunis->getOffset(new \DateTime('now',$tunis))) / 3600;
 $timezone = ['heure'=>$now->format('H:i'), 'date'=>$now->format('d/m/Y'), 'zone'=>$tz,
 'decalage'=>$diff==0?'Meme fuseau que Tunis':($diff>0?'+'.$diff.'h / Tunis':$diff.'h / Tunis'),
 'moment'=>($now->format('H')>=6 && $now->format('H')<20)?'Jour':'Nuit'];
 } catch (\Exception $e) {}
 }
 return $this->render('voyage/show.html.twig', [
 'voyage' => $voyage, 'meteo' => $meteo, 'timezone' => $timezone,
 ]);
}

   #[Route('/voyage/recommandations-ia', name: 'app_voyage_recommandations_ia')]
public function recommandationsIA(Request $request, VoyageRepository $voyageRepository): Response
{
    $suggestion = null;
    $budget = $request->query->get('budget');
    $type = $request->query->get('type');
    $duree = $request->query->get('duree');

    if ($budget || $type || $duree) {
        $voyages = $voyageRepository->findAll();
        $voyagesText = '';
        foreach ($voyages as $v) {
            $voyagesText .= "- {$v->getTitre()} : {$v->getPrix()} TND, {$v->getNb_places()} places, du " .
                ($v->getDate_debut() ? $v->getDate_debut()->format('d/m/Y') : 'N/A') . " au " .
                ($v->getDate_fin() ? $v->getDate_fin()->format('d/m/Y') : 'N/A') .
                ", description: {$v->getDescription()}\n";
        }

        $prompt = "Tu es un conseiller en voyages expert. Voici les voyages disponibles :\n$voyagesText\n" .
            "L'utilisateur cherche : budget maximum=" . ($budget ?? 'non spécifié') . " TND, " .
            "type de voyage=" . ($type ?? 'non spécifié') . ", " .
            "durée souhaitée=" . ($duree ?? 'non spécifiée') . " jours.\n" .
            "Recommande 2-3 voyages de la liste avec une explication courte et personnalisée en français. " .
            "Si aucun voyage ne correspond, explique pourquoi et donne des conseils.";

        try {
            $client = \Symfony\Component\HttpClient\HttpClient::create();
            $response = $client->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'apikey',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'llama3-8b-8192',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'max_tokens' => 500,
                    'temperature' => 0.7,
                ]
            ]);
            $data = $response->toArray();
            $suggestion = $data['choices'][0]['message']['content'] ?? 'Aucune suggestion disponible.';
        } catch (\Exception $e) {
            $suggestion = 'Erreur : ' . $e->getMessage();
        }
    }

    return $this->render('voyage/recommandations_ia.html.twig', [
        'suggestion' => $suggestion,
        'voyagesRecommandes' => [],
        'budget' => $budget,
        'type' => $type,
        'duree' => $duree,
    ]);
>>>>>>> Stashed changes
}
}
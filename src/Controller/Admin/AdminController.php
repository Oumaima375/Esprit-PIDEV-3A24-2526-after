<?php
namespace App\Controller\Admin;
use App\Repository\VoyageRepository;
use App\Repository\DestinationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
class AdminController extends AbstractController
{
 #[Route('/admin', name: 'admin_dashboard')]
 public function index(VoyageRepository $voyageRepo, DestinationRepository $destRepo): Response
 {
 $allVoyages = $voyageRepo->findAll();
 $totalRevenue = 0;
 $totalPlaces = 0;
foreach ($allVoyages as $v) { $totalPlaces += $v->getNb_places(); }
 foreach ($allVoyages as $v) { $totalRevenue += $v->getPrix(); }
 $prixMoyen = count($allVoyages) > 0 ? round($totalRevenue / count($allVoyages)) : 0;
 // Voyages par destination (stats)
 $destinations = $destRepo->findAll();
 $statsDestinations = [];
 foreach ($destinations as $d) {
 $statsDestinations[] = [
 'pays' => $d->getPays(),
 'count' => count($voyageRepo->findBy(['id_destination' => $d]))
 ];
 }
 usort($statsDestinations, fn($a,$b) => $b['count'] - $a['count']);
 return $this->render('admin/dashboard.html.twig', [
 'totalVoyages' => count($allVoyages),
 'totalDestinations' => count($destinations),
 'prixMoyen' => $prixMoyen,
 'recentVoyages' => $voyageRepo->findBy([], ['id_voyage' => 'DESC'], 5),
 'statsDestinations' => array_slice($statsDestinations, 0, 5),
 'totalPlaces' => $totalPlaces,
 ]);
 }
}
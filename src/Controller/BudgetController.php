<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\DepenseRepository;
use App\Service\BudgetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BudgetController extends AbstractController
{
    #[Route('/budget/status', name: 'budget_status')]
    public function status(CategorieRepository $catRepo, DepenseRepository $depRepo, BudgetService $budgetService): JsonResponse
    {
        $categories = $catRepo->findAll();
        $result = [];

        foreach ($categories as $cat) {
            $total = $depRepo->getTotalByCategorie($cat->getId());
            $budget = $budgetService->getBudget($cat->getId());

            $result[] = [
                'id' => $cat->getId(),
                'nom' => $cat->getNomCategorie(),
                'total' => $total,
                'budgetMax' => $budget,
                'depasse' => $budget ? $total > $budget : false,
                'enabled' => $budget !== null
            ];
        }

        return $this->json($result);
    }

    #[Route('/budget/update-all', name: 'budget_update_all', methods: ['POST'])]
    public function updateAll(Request $request, BudgetService $budgetService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $budgets = $data['budgets'] ?? [];

        foreach ($budgets as $id => $info) {
            $enabled = $info['enabled'] ?? false;
            $montant = $enabled ? ($info['montant'] ?? null) : null;
            $budgetService->setBudget((int)$id, $montant);
        }

        return $this->json(['success' => true]);
    }

    #[Route('/budget/global', name: 'budget_global')]
    public function getGlobalBudget(BudgetService $budgetService): JsonResponse
    {
        return $this->json(['budgetGlobalMax' => $budgetService->getGlobalBudget()]);
    }

    #[Route('/budget/global/save', name: 'budget_global_save', methods: ['POST'])]
    public function saveGlobalBudget(Request $request, BudgetService $budgetService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $budgetGlobal = $data['budgetGlobalMax'] ?? null;
        $budgetService->setGlobalBudget($budgetGlobal);
        return $this->json(['success' => true]);
    }

    #[Route('/depense/total-global', name: 'depense_total_global')]
    public function getTotalGlobal(DepenseRepository $depRepo): JsonResponse
    {
        $total = $depRepo->getTotalGlobal();
        return $this->json(['total' => $total]);
    }

    #[Route('/budget/category-status/{id}', name: 'budget_category_status')]
    public function getCategoryStatus(int $id, CategorieRepository $catRepo, DepenseRepository $depRepo, BudgetService $budgetService): JsonResponse
    {
        $cat = $catRepo->find($id);
        if (!$cat) {
            return $this->json(['error' => 'Catégorie introuvable'], 404);
        }
        $total = $depRepo->getTotalByCategorie($id);
        $budgetMax = $budgetService->getBudget($id);
        
        return $this->json([
            'id' => $id,
            'nom' => $cat->getNomCategorie(),
            'total' => $total,
            'budgetMax' => $budgetMax,
            'depassement' => $budgetMax !== null ? ($total >= $budgetMax) : false
        ]);
    }
}
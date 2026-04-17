<?php

namespace App\Service;

class BudgetService
{
    private string $filePath;
    private array $budgets = [];

    public function __construct(string $projectDir)
    {
        $this->filePath = $projectDir . '/var/budgets.json';
        $this->load();
    }

    private function load(): void
    {
        if (file_exists($this->filePath)) {
            $content = file_get_contents($this->filePath);
            $this->budgets = json_decode($content, true) ?: [];
        }
    }

    private function save(): void
    {
        file_put_contents($this->filePath, json_encode($this->budgets, JSON_PRETTY_PRINT));
    }

    public function getBudget(int $categorieId): ?float
    {
        return $this->budgets[$categorieId] ?? null;
    }

    public function setBudget(int $categorieId, ?float $max): void
    {
        if ($max === null) {
            unset($this->budgets[$categorieId]);
        } else {
            $this->budgets[$categorieId] = $max;
        }
        $this->save();
    }

    public function getAllBudgets(): array
    {
        return $this->budgets;
    }
}
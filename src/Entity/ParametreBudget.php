<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'parametre_budget')]
class ParametreBudget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'budget_global_max', nullable: true)]
    private ?float $budgetGlobalMax = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBudgetGlobalMax(): ?float
    {
        return $this->budgetGlobalMax;
    }

    public function setBudgetGlobalMax(?float $budgetGlobalMax): static
    {
        $this->budgetGlobalMax = $budgetGlobalMax;
        return $this;
    }
}
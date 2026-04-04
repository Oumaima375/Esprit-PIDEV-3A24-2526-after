<?php

namespace App\Repository;

use App\Entity\Categorie_document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class Categorie_documentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie_document::class);
    }

    // Add custom methods as needed
}
<?php
// src/Repository/DocumentHistoriqueRepository.php

namespace App\Repository;

use App\Entity\DocumentHistorique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;



class DocumentHistoriqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentHistorique::class);
    }

    public function findByDocument(int $idDocument): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.idDocument = :id')
            ->setParameter('id', $idDocument)
            ->orderBy('h.dateAction', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
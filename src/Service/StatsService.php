<?php 

// src/Service/StatsService.php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\File;
use App\Entity\User;
use DateTime;

class StatsService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Nombre total de fichiers uploadés
    public function getTotalFilesCount(): int
    {
        return $this->entityManager->getRepository(File::class)->count([]);
    }

    // Nombre de fichiers uploadés aujourd'hui
    public function getTodayFilesCount(): int
    {
        $today = new DateTime();
        $startOfDay = $today->setTime(0, 0, 0);

        return $this->entityManager->getRepository(File::class)
            ->createQueryBuilder('f')
            ->select('count(f.id)')
            ->where('f.ajoutfile >= :start')
            ->setParameter('start', $startOfDay)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Répartition du nombre de fichiers par utilisateur
    public function getFilesCountByUser(): array
    {
        return $this->entityManager->getRepository(File::class)
            ->createQueryBuilder('f')
            ->select('u.nom as user_name, count(f.id) as file_count')
            ->join('f.user', 'u')
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();
    }
}

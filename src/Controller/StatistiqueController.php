<?php

namespace App\Controller;

use App\Service\StatsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StatistiqueController extends AbstractController
{
    private $statsService;

    public function __construct(StatsService $statsService)
    {
        $this->statsService = $statsService;
    }



    #[Route('/statistique', name: 'app_statistique')]
    public function index(): Response
    {
        $totalFiles = $this->statsService->getTotalFilesCount();
        $todayFiles = $this->statsService->getTodayFilesCount();
        $filesByUser = $this->statsService->getFilesCountByUser();



        return $this->render('statistique/index.html.twig', [

            'total_files' => $totalFiles,
            'today_files' => $todayFiles,
            'files_by_user' => $filesByUser,
        ]);  
       
    }
}

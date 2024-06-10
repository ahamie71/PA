<?php

namespace App\Controller;

use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route('/admin/files', name: 'admin_files')]
    public function listFiles(EntityManagerInterface $entityManager, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $format = $request->query->get('format', '');
        $sortBy = $request->query->get('sort_by', 'ajoutfile');
        $order = $request->query->get('order', 'asc');

        $query = $this->entityManager->createQueryBuilder()
            ->select('f')
            ->from(File::class, 'f');

        if ($search) {
            $query->andWhere('f.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($format) {
            $query->andWhere('f.format = :format')
                ->setParameter('format', $format);
        }

        if (in_array($sortBy, ['ajoutfile', 'poids'])) {
            $query->orderBy('f.' . $sortBy, $order);
        }

        $files = $query->getQuery()->getResult();

        // Get distinct formats for filter options
        $distinctFormats = $this->entityManager->createQueryBuilder()
            ->select('DISTINCT f.format')
            ->from(File::class, 'f')
            ->getQuery()
            ->getResult();

        return $this->render('admin/fichiers.html.twig', [
            'files' => $files,
            'formats' => $distinctFormats,
        ]);
    }

    #[Route('/admin/files/view/{id}', name: 'admin_files_view')]
    public function view(int $id): BinaryFileResponse
    {
        $file = $this->entityManager->getRepository(File::class)->find($id);

        if (!$file) {
            throw $this->createNotFoundException('File not found');
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public' . $file->getChemain();

        return new BinaryFileResponse($filePath, 200, [], true);
    }

    #[Route('/admin/files/download/{id}', name: 'admin_files_download')]
    public function downloadFile(int $id): Response
    {
        $file = $this->entityManager->getRepository(File::class)->find($id);

        if (!$file) {
            throw $this->createNotFoundException('File not found');
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public' . $file->getChemain();

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getNom());

        return $response;
    }

    #[Route('/admin/files/delete/{id}', name: 'admin_files_delete')]
    public function deleteFile(int $id): Response
    {
        $file = $this->entityManager->getRepository(File::class)->find($id);

        if (!$file) {
            throw $this->createNotFoundException('File not found');
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public' . $file->getChemain();

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->entityManager->remove($file);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_files');
    }
}

<?php

namespace App\Controller;

use App\Entity\File;
use App\Repository\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class FileController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route('/file', name: 'app_file')]
    public function index( EntityManagerInterface $entityManager, Request $request,FileRepository $fileRepository, Security $security): Response
   {
 
        


            $user = $security->getUser();
            if (!$user) {
                throw $this->createAccessDeniedException('You must be logged in to access this page.');
            }
    
            $search = $request->query->get('search', '');
            $format = $request->query->get('format', '');
            $sortBy = $request->query->get('sort_by', 'ajoutfile');
            $order = $request->query->get('order', 'asc');
    
            $query = $entityManager->createQueryBuilder()
                ->select('f')
                ->from(File::class, 'f')
                ->where('f.user = :user')
                ->setParameter('user', $user);
    
            if ($search) {
                $query->andWhere('f.nom LIKE :search')
                    ->setParameter('search', '%' . $search . '%');
            }
    
            if ($format) {
                $query->andWhere('f.format = :format')
                    ->setParameter('format', $format);
            }
    
            $query->orderBy('f.' . $sortBy, $order);
    
            $files = $query->getQuery()->getResult();
    
            // Get distinct formats for filter options
            $distinctFormats = $entityManager->createQueryBuilder()
                ->select('DISTINCT f.format')
                ->from(File::class, 'f')
                ->where('f.user = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult();
    
            return $this->render('file/index.html.twig', [
                'files' => $files,
                'user' => $user,
                'formats' => $distinctFormats,
            ]);
        }

        #[Route('/file/{id}/delete', name: 'delete_file')]
        public function delete(File $file, EntityManagerInterface $entityManager): Response
        {
            // Vérifiez si le fichier existe
            if (!$file) {
                throw $this->createNotFoundException('File not found');
            }
    
            // Supprimez le fichier de la base de données
            $entityManager->remove($file);
            $entityManager->flush();
    
            // Redirigez vers la page précédente ou une autre page appropriée
            return $this->redirectToRoute('app_file');
        }


        #[Route('/file/download/{id}', name: 'download_file')]
        public function download(int $id): BinaryFileResponse
        {
            $file = $this->entityManager->getRepository(File::class)->find($id);
    
            if (!$file) {
                throw $this->createNotFoundException('File not found');
            }
    
            $filePath = $this->getParameter('kernel.project_dir') . '/public' . $file->getChemain();
    
            return $this->file($filePath, $file->getNom(), ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        }


        #[Route('/file/{id}/view', name: 'view_file')]
public function view(File $file): Response
{
    // Vérifie si le fichier existe
    if (!$file) {
        throw $this->createNotFoundException('File not found');
    }

    // Récupère le chemin complet du fichier
    $filePath = $this->getParameter('kernel.project_dir') . '/public' . $file->getChemain();

    // Retourne une réponse pour afficher le fichier
    return new BinaryFileResponse($filePath, 200, [], true);
}
    }
    


    

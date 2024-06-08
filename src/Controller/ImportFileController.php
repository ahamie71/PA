<?php

namespace App\Controller;

use App\Entity\File;
use App\Form\FileUploadType;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ImportFileController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/import/file', name: 'app_import_file')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $uploadedFile = $request->files->get('file');
        if ($uploadedFile) {
            $uploadDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads';
            $fileName = uniqid() . '.' . $uploadedFile->guessExtension();
            try {

                $fileSize = $uploadedFile->getSize();

                if ($fileSize === false) {
                    throw new \Exception("Impossible de récupérer la taille du fichier.");
                }
    
                // Convertir la taille en entier
              
                $fileSizeMB = $fileSize / (1024 * 1024);
                
                if ($user->getStorageSpace() < $fileSizeMB) {
                    $this->addFlash('error', "Espace de stockage insuffisant. Veuillez vous rendre sur la <a href='" . $this->generateUrl('app_achat') . "'>page d'achat d'espace de stockage</a>.");
                    throw new \Exception("Espace de stockage insuffisant.");
                }
                    $uploadedFile->move($uploadDirectory, $fileName);
                    // Créer une nouvelle entité File et définir son chemin de fichier
                    $file = new File();
                    $file->setNom($uploadedFile->getClientOriginalName());
                    $file->setChemain('/uploads/'. $fileName);
                    $file->setPoids($fileSize);
                    $file->setAjoutfile(new \DateTime());
                    $file->setFormat($uploadedFile->getClientOriginalExtension());
                    $file->setUser($user);  
                    $entityManager->persist($file);
                    $user->setStorageSpace($user->getStorageSpace() - $fileSizeMB);
                    $entityManager->persist($user);

                    $entityManager->flush();
                    
                    $this->addFlash('success', 'Fichier téléchargé avec succès.');

                } catch (\Exception $e) {
                   


                }
            } else {
                // $this->addFlash('error', 'Aucun fichier n\'a été soumis.');
            }
    
            return $this->render('import_file/index.html.twig');
            
        }
    }

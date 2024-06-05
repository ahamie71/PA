<?php

namespace App\Controller;

use App\Entity\File;
use App\Form\FileUploadType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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

            // Récupérer le fichier uploadé
            $uploadedFile = $request->files->get('file');
    
            // Vérifier si un fichier a été soumis
            if ($uploadedFile) {
                // Gérer l'upload du fichier
                $uploadDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads';
                $fileName = uniqid() . '.' . $uploadedFile->guessExtension();
    
                try {
                    $uploadedFile->move($uploadDirectory, $fileName);
    
                    // Créer une nouvelle entité File et définir son chemin de fichier
                    $file = new File();
                 
                    $file->setNom($uploadedFile->getClientOriginalName());
                    $file->setChemain('/uploads/'. $fileName); 
                  
                    $file->setPoids($uploadedFile->getSize());
                   
                    
                    $file->setAjoutfile(new \DateTime());
                  
                    $file->setFormat($uploadedFile->getClientOriginalExtension());
                     
                    $file->setUser($user);
                   
                    // Persister l'entité
                
                    $entityManager->persist($file);
                    
                    $entityManager->flush();

                    $this->addFlash('success', 'Le fichier a été uploadé avec succès.');

                    
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'upload du fichier.');
                }
            } else {
                $this->addFlash('error', 'Aucun fichier n\'a été soumis.');
            }
    
            return $this->render('import_file/index.html.twig');
            
        }
    }

 



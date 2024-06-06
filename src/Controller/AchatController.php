<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Facture;
use App\Form\AchatType;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AchatController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }






    #[Route('/achat', name: 'app_achat')]
    public function purchaseSpace(Request $request ,EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur actuel
        $user = $this->getUser();

        // Créer le formulaire d'achat d'espace
        $form = $this->createForm(AchatType::class);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour l'espace de stockage de l'utilisateur
            $newStorageSpace = $user->getStoragespace() + 20;
            $user->setStoragespace($newStorageSpace);

            // Enregistrer les modifications dans la base de données
            
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre achat a été effectué avec succès.');
            
        }

        // Afficher le formulaire d'achat d'espace
        return $this->render('achat/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }






    // #[Route('/confirmation-achat', name: 'confirmation_achat')]
    // public function confirmationAchat(): Response
    // {
    //     return $this->render('achat/confirmationAchat.html.twig');
    // }
}

    


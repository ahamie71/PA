<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Company;
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
        $user = $this->getUser();
        $form = $this->createForm(AchatType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newStorageSpace = $user->getStoragespace() + 20;
            $user->setStoragespace($newStorageSpace);
            $entityManager->persist($user);
            $societe = $entityManager->getRepository(Company::class)->find(1);
            $prixUnitaireHT = 20; 
            $montantTVA = 100; 
            $montantTotalTTC = $prixUnitaireHT + $montantTVA;
            $facture = new Facture();
            $facture->setUser($user);
            $facture->setDate(new \DateTime());
            $facture->setDesignation('Espace de stockage supplémentaire');
            $facture->setPrixunitaireht($prixUnitaireHT);
            $facture->setQuantite(20); 
            $facture->setMontanttva($montantTVA); 
            $facture->setMontanttotalttc($montantTotalTTC);
            if ($societe) {
                $facture->setCompany($societe);
            }
            $entityManager->persist($facture);
            $entityManager->flush();
            $this->addFlash('success', 'Votre achat a été effectué avec succès.'); 
            
        }
        return $this->render('achat/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }


}

    


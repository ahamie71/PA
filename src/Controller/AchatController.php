<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Company;
use App\Entity\Facture;
use App\Form\AchatType;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AchatController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/achat', name: 'app_achat')]
    public function purchaseSpace(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(AchatType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mise à jour de l'espace de stockage de l'utilisateur
            $newStorageSpace = $user->getStoragespace() + 20;
            $user->setStoragespace($newStorageSpace);
            $entityManager->persist($user);

            // Création de la facture
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

            // Génération du PDF de la facture
            $pdfOptions = new Options();
            $pdfOptions->set('defaultFont', 'Arial');

            $dompdf = new Dompdf($pdfOptions);

            $html = $this->renderView('facture/facture_pdf.html.twig', [
                'facture' => $facture,
            ]);

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Enregistrer le PDF généré dans une variable
            $pdfOutput = $dompdf->output();

            $nomFichierPDF = 'facture_' . $user->getNom() . '_' . (new \DateTime())->format('Ymd_His') . '.pdf';

            // Envoi de l'e-mail de confirmation avec la facture attachée
            $email = (new Email())
                ->from('no-reply@example.com')  // Remplacez par votre adresse e-mail d'expéditeur
                ->to($user->getEmail())  // Utilisez l'adresse e-mail de l'utilisateur connecté
                ->subject('Votre achat a été effectué avec succès')
                ->text('Merci pour votre achat ! Veuillez trouver votre facture ci-jointe.')
                ->attach($pdfOutput, $nomFichierPDF, 'application/pdf');

            $mailer->send($email);

            $this->addFlash('success', 'Votre achat a été effectué avec succès. Un email de confirmation avec la facture a été envoyé à votre adresse.');

            // Redirection ou autre logique
        }

        return $this->render('achat/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Facture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FactureController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route('/facture', name: 'app_facture')]
    public function mesFactures(): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            $this->redirectToRoute('app_login');
        }

        // Récupérer toutes les factures associées à cet utilisateur
        $factures = $user->getFactures();

        // Passer les factures à la vue pour les afficher
        return $this->render('facture/facture_template.html.twig', [
            'factures' => $factures,
        ]);
    }

    #[Route('/facture/pdf/{id}', name: 'app_facture_pdf')]
    public function facturePdf(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer la facture spécifique
        $facture = $entityManager->getRepository(Facture::class)->find($id);

        // Vérifier si la facture appartient à l'utilisateur connecté
        if (!$facture || $facture->getUser() !== $user) {
            throw $this->createNotFoundException('Facture non trouvée');
        }

        // Configurer Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instancier Dompdf avec nos options
        $dompdf = new Dompdf($pdfOptions);

        // Générer le HTML pour le PDF
        $html = $this->renderView('facture/facture_pdf.html.twig', [
            'facture' => $facture,
        ]);

        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);

        // (Optionnel) Configurer la taille et l'orientation du papier
        $dompdf->setPaper('A4', 'portrait');

        // Rendre le PDF
        $dompdf->render();

        // Sortie du PDF généré dans un fichier
        $output = $dompdf->output();

        // Créer une réponse HTTP pour télécharger le PDF
        return new Response(
            $output,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="facture_'.$facture->getUser()->getNom().'.pdf"',
            ]
        );
    }
}
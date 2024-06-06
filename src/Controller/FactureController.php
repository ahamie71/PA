<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FactureController extends AbstractController
{
    #[Route('/facture', name: 'app_facture')]
    public function mesFactures(): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            // Gérer le cas où l'utilisateur n'est pas connecté
            // Rediriger vers une page de connexion par exemple
        }

        // Récupérer toutes les factures associées à cet utilisateur
        $factures = $user->getFactures();

        // Passer les factures à la vue pour les afficher
        return $this->render('facture/facture_template.html.twig', [
            'factures' => $factures,
        ]);
    }
}

<?php


namespace App\Controller;

use App\Entity\Facture; 
use App\Entity\File;
use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_user_profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Créez le formulaire et liez-le à l'utilisateur actuel
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si le formulaire est soumis et valide, mettez à jour les informations de l'utilisateur
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('profile/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile/delete', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $user = $this->getUser();

        if ($user) {
            // Supprimer les fichiers associés à l'utilisateur
            $files = $entityManager->getRepository(File::class)->findBy(['user' => $user]);
            foreach ($files as $file) {
                $entityManager->remove($file);
            }

            // Supprimer les factures associées à l'utilisateur
            $factures = $entityManager->getRepository(Facture::class)->findBy(['user' => $user]);
            foreach ($factures as $facture) {
                $entityManager->remove($facture);
            }

            // Supprimer l'utilisateur
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre compte a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_logout');
    }
}

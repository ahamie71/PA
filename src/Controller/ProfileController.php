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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;


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
public function delete(Request $request,MailerInterface $mailer , EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, SessionInterface $session): RedirectResponse
{
    $user = $this->getUser();

    if ($user) {
        // Initialiser le compteur de fichiers supprimés
        $fileCount = 0;

        // Supprimer les fichiers associés à l'utilisateur
        $files = $entityManager->getRepository(File::class)->findBy(['user' => $user]);
        $fileCount = count($files); // Mettre à jour le nombre de fichiers supprimés
        foreach ($files as $file) {
            $entityManager->remove($file);
        }

        // Supprimer les factures associées à l'utilisateur
        $factures = $entityManager->getRepository(Facture::class)->findBy(['user' => $user]);
        foreach ($factures as $facture) {
            $entityManager->remove($facture);
        }

         // Envoyer un email de confirmation de suppression de compte
         $email = (new Email())
         ->from('no-reply@example.com')  // Remplacez par votre adresse e-mail d'expéditeur
         ->to($user->getEmail())  // Adresse e-mail de l'utilisateur supprimé
         ->subject('Votre compte a été supprimé')
         ->text('Nous vous confirmons que votre compte a été supprimé. Merci d\'avoir utilisé nos services.');

         $mailer->send($email);

        // Supprimer l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Votre compte a été supprimé avec succès.');

        // Déconnecter l'utilisateur et invalider la session
        $tokenStorage->setToken(null);
        $session->invalidate();

          $adminUsers = $entityManager->getRepository(User::class)->findByRole('ROLE_ADMIN');
            foreach ($adminUsers as $admin) {
                $adminEmail = (new Email())
                    ->from('no-reply@example.com') // Remplacez par votre adresse e-mail d'expéditeur
                    ->to($admin->getEmail())  // Adresse e-mail de l'administrateur
                    ->subject('Notification de suppression de compte')
                    ->text(sprintf(
                        'L\'utilisateur %s %s a supprimé son compte. Nombre de fichiers supprimés : %d.',
                        $user->getNom(),
                        $user->getPrenom(),
                        $fileCount
                    ));

                $mailer->send($adminEmail);
            }
    }

    // Rediriger vers la page de connexion
    return $this->redirectToRoute('app_login');
}

}

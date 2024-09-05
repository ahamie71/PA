<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;



class InscriptionController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
     


    

    #[Route('/inscription', name: 'app_inscription')]
    public function index(Request $request ,UserPasswordHasherInterface $encoder): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user_find = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());

            if (!$user_find) {
                // Hasher le mot de passe
                $password = $encoder->hashPassword($user, $user->getPassword());
                $user->setPassword($password);
                $user->setDate(new \DateTime());
                if ($form->get('storagespace')->getData()) {
                    $user->setStoragespace(20);
                    $user->setRoles(["ROLE_USER"]);
                }
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('error', 'Cet utilisateur existe déjà.');

            }
        }

        return $this->render('inscription/index.html.twig', [
            'form' => $form->createView(),
            
        ]);
    
    }




}

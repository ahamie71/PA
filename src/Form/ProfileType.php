<?php
// src/Form/ProfileType.php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

        ->add('nom', TextType::class, [
            'label' => 'Nom',
        ])
        ->add('prenom', TextType::class, [
            'label' => 'Prénom',
        ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
          
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
            ])
            ->add('roles', TextType::class, [
                'label' => 'Rôles',
                'disabled' => true,
                'data' => implode(', ', $options['data']->getRoles()),
                'attr' => ['readonly' => true],
            ])
            // ->add('password', TextType::class, [
            //     'label' => 'Mot de passe',
            //     'disabled' => true,
            //     'attr' => ['readonly' => true],
            // ])
            ->add('date', DateType::class, [
                'label' => 'Date de création du compte',
                'widget' => 'single_text',
                'disabled' => true,
                'attr' => ['readonly' => true],
            ])
            ->add('storagespace', TextType::class, [
                'label' => 'Espace de stockage (GB)',
                'disabled' => true,
                'attr' => ['readonly' => true],
            ])

            ->add('submit', SubmitType::class, [
                'label' => "Modifier",
                'attr' => [
                    'class' => 'btn w-100 text-white mt-2 btn-lg bg-success',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

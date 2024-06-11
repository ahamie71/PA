<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class AdminUserType extends AbstractType
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
        ->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'The password and the confirmation must be identical.',
            'required' => true,
            'first_options' => [
                'label' => 'Password',
                'attr' => [
                    'class' => 'form-control',
                ],
            ],
            'second_options' => [
                'label' => 'Confirm your password',
                'attr' => [
                    'class' => 'form-control',
                ],
            ],
        ])
        ->add('storagespace', CheckboxType::class, [
            'label' => 'Buy additional storage space (20€ for 20GB)',
            'required' => false,
        ])
        ->add('roles', ChoiceType::class, [
            'label' => 'Role',
            'choices' => [
                'User' => 'ROLE_USER',
                'Admin' => 'ROLE_ADMIN',
            ],
            'multiple' => true,
            'expanded' => true,
        ])
        ->add('submit', SubmitType::class, [
            'label' => "S'inscrire",
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

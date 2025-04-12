<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Votre prénom',
                    'class' => 'form-control'
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Votre nom',
                    'class' => 'form-control'
                ]
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'email@exemple.com',
                    'class' => 'form-control'
                ]
            ])
            ->add('password', PasswordType::class, [
                'required' => false,
                'always_empty' => true,
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control'
                ],
                'label' => 'Mot de passe'
            ])
            ->add('genre', ChoiceType::class, [
                'choices' => [
                    'Homme' => 'homme',
                    'Femme' => 'femme',
                    'Autre' => 'autre'
                ],
                'placeholder' => 'Choisir un genre',
                'required' => false,
                'label' => 'Genre',
                'attr' => ['class' => 'form-select']
            ])
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'html5' => true,
                'label' => 'Date de naissance',
                'attr' => ['class' => 'form-control']
            ])
            ->add('phoneNumber', TextType::class, [
                'required' => false,
                'label' => 'Téléphone',
                'attr' => [
                    'placeholder' => '+33 6 12 34 56 78',
                    'class' => 'form-control'
                ]
            ])
            ->add('adresse', TextType::class, [
                'required' => false,
                'label' => 'Adresse',
                'attr' => ['class' => 'form-control']
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                
                    'Admin' => 'ADMIN',
                    'Responsable' => 'RESPONSABLE',
                    'Employé' => 'EMPLOYEE'
                ],
                'placeholder' => 'Choose a role',
                'required' => false,
                'multiple' =>true,
                'label' => 'Role',
                'attr' => ['class' => 'form-select']
            ])
            // ->add('roles'
            // , ChoiceType::class, [
            //     'choices' => [
            //         'Admin' => 'ADMIN',
            //         'Responsable' => 'RESPONSABLE',
            //         'Employee' => 'EMPLOYEE'
            //     ],
            //     'label' => 'Rôle',
            //     'attr' => ['class' => 'form-select'],
            //     'multiple' => false,
            //     'expanded' => false
            // ]
            // )
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Actif' => 'actif',
                    'Inactif' => 'inactif'
                ],
                'label' => 'Statut',
                'attr' => ['class' => 'form-select']
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Photo de profil',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('privileges', TextType::class, [
                'required' => false,
                'label' => 'Privilèges (admin)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('poste', TextType::class, [
                'required' => false,
                'label' => 'Poste (employé)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('departement', TextType::class, [
                'required' => false,
                'label' => 'Département (responsable)',
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            
        ]);

    }
}
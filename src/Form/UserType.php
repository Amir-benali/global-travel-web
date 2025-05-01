<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\File;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Votre prénom',
                    'class' => 'form-control',
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Votre nom',
                    'class' => 'form-control',
                ]
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'email@exemple.com',
                    'class' => 'form-control',
                ]
            ])
            ->add('genre', ChoiceType::class, [
                'choices' => [
                    'Male' => 'male',
                    'Female' => 'female',
                    'Other' => 'other'
                ],
                'placeholder' => 'Choose your gender',
                'required' => true,
                'label' => 'Gender',
                'attr' => ['class' => 'form-select']
            ])
            
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'html5' => true,
                'label' => 'Date de naissance',
                'attr' => ['class' => 'form-control'],
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
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an address',
                    ]),
                    new Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => 'The address must be at least {{ limit }} characters long',
                        'maxMessage' => 'The address must not exceed {{ limit }} characters',
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9\s,.\'-]{5,}$/',
                        'message' => 'The address should contain only letters, numbers, spaces, and basic punctuation',
                    ]),
                ],
            ]);

            if (!empty($options['is_settings']) && $options['is_settings'] === true) {
                $builder->add('profilePicture', FileType::class, [
                    'label' => 'Photo de profil',
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'accept' => 'image/*',
                        'class' => 'form-control',
                    ],
                    'constraints' => [
                        new File([
                            'maxSize' => '2M',
                            'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                            'mimeTypesMessage' => 'Please upload a valid image (jpeg, png, webp)',
                        ]),
                    ],
                ]);
            }

        if (!$options['is_settings']) {
            $builder->add('roles', ChoiceType::class, [
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    'Responsable' => 'ROLE_RESPONSABLE',
                    'Employé' => 'ROLE_EMPLOYEE'
                ],
                'placeholder' => 'Choose a role',
                'required' => false,
                'multiple' => true,
                'label' => 'Role',
                'attr' => ['class' => 'form-select']
            ]);
        }

        $builder->add('statut', ChoiceType::class, [
            'choices' => [
                'Actif' => 'actif',
                'Inactif' => 'inactif'
            ],
            'label' => 'Statut',
            'attr' => ['class' => 'form-select']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_new' => false,
            'is_settings' => false,
        ]);
    }
}
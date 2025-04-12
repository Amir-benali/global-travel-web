<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AuthType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isLogin = $options['is_login'] ?? false;

        if ($isLogin) {
            // Login form
            $builder
                ->add('_username', EmailType::class, [
                    'label' => 'Email',
                    'mapped' => false,
                    'attr' => [
                        'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500',
                        'placeholder' => 'Enter your email',
                        'autocomplete' => 'email'
                    ],
                    'constraints' => [
                        new NotBlank(['message' => 'Please enter your email'])
                    ]
                ])
                ->add('_password', PasswordType::class, [
                    'label' => 'Password',
                    'mapped' => false,
                    'attr' => [
                        'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500',
                        'placeholder' => 'Enter your password',
                        'autocomplete' => 'current-password'
                    ],
                    'constraints' => [
                        new NotBlank(['message' => 'Please enter your password'])
                    ]
                ]);
        } else {
            // Register form
            $builder
                ->add('firstname', TextType::class, [
                    'label' => 'First Name',
                    'attr' => [
                        'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500',
                        'placeholder' => 'John'
                    ],
                    'constraints' => [
                        new NotBlank(['message' => 'Please enter your first name']),
                        new Length(['min' => 2, 'max' => 50])
                    ]
                ])
                ->add('lastname', TextType::class, [
                    'label' => 'Last Name',
                    'attr' => [
                        'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500',
                        'placeholder' => 'Doe'
                    ],
                    'constraints' => [
                        new NotBlank(['message' => 'Please enter your last name']),
                        new Length(['min' => 2, 'max' => 50])
                    ]
                ])
                ->add('email', EmailType::class, [
                    'label' => 'Email',
                    'attr' => [
                        'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500',
                        'placeholder' => 'john.doe@example.com'
                    ],
                    'constraints' => [
                        new NotBlank(['message' => 'Please enter your email']),
                        new Length(['max' => 180])
                    ]
                ])
                ->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'The password fields must match.',
                    'options' => ['attr' => [
                        'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500'
                    ]],
                    'required' => true,
                    'first_options'  => [
                        'label' => 'Password',
                        'attr' => ['placeholder' => 'Create a password']
                    ],
                    'second_options' => [
                        'label' => 'Repeat Password',
                        'attr' => ['placeholder' => 'Confirm your password']
                    ],
                    'constraints' => [
                        new NotBlank(['message' => 'Please enter a password']),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            'max' => 4096
                        ])
                    ],
                ])
                ->add('genre', ChoiceType::class, [
                    'label' => 'Gender',
                    'choices' => [
                        'Male' => 'male',
                        'Female' => 'female',
                        'Other' => 'other'
                    ],
                    'placeholder' => 'Choose your gender',
                    'attr' => [
                        'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500'
                    ]
                ])
                ->add('phoneNumber', TextType::class, [
                    'label' => 'Phone Number',
                    'required' => false,
                    'attr' => [
                        'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500',
                        'placeholder' => 'Phone number'
                    ]
                ])
                ->add('dateNaissance', DateType::class, [
                    'label' => 'Birth Date',
                    'widget' => 'single_text',
                    'html5' => true,
                    'attr' => [
                        'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500'
                    ]
                ])
                ->add('imageFile', FileType::class, [
                    'label' => 'Profile Image',
                    'required' => false,
                    'mapped' => false
                ])
                ->add('agreeTerms', CheckboxType::class, [
                    'mapped' => false,
                    'label' => 'I agree to the terms and conditions',
                    'constraints' => [
                        new IsTrue(['message' => 'You should agree to our terms.']),
                    ],
                    'attr' => [
                        'class' => 'h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:focus:ring-primary-500'
                    ]
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_login' => false
        ]);
    }
}

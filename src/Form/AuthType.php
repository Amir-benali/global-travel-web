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
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Regex;


class AuthType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isLogin = $options['is_login'] ?? false;

        if ($isLogin) {
            // Login form
           
        $builder
        ->add('email', EmailType::class, [
            'label' => 'Email address',
            'attr' => [
                'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500',
                'placeholder' => 'your.email@example.com',
                'autocomplete' => 'email',
                'data-parsley-trigger' => 'change',
            ],
            'constraints' => [
                new NotBlank(['message' => 'Please enter your email']),
                new Email(['message' => 'Please enter a valid email address']),
            ],
        ])
        ->add('password', PasswordType::class, [
            'label' => 'Password',
            'attr' => [
                'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500',
                'placeholder' => '••••••••',
                'autocomplete' => 'current-password',
                'data-parsley-trigger' => 'change',
            ],
            'constraints' => [
                new NotBlank(['message' => 'Please enter your password']),
            ],
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
                    new Assert\NotBlank(['message' => 'Please enter your first name']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 15,
                        'maxMessage' => 'First name should not be longer than {{ limit }} characters'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z\s\-]+$/',
                        'message' => 'First name should contain only letters'
                    ])
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Last Name',
                'attr' => [
                    'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500',
                    'placeholder' => 'Doe'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your last name']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 15,
                        'maxMessage' => 'Last name should not be longer than {{ limit }} characters'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z\s\-]+$/',
                        'message' => 'Last name should contain only letters'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500',
                    'placeholder' => 'john.doe@example.com'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your email']),
                    new Assert\Length(['max' => 180]),
                    new Assert\Email(['message' => 'Please enter a valid email address'])
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'password-field']],
                'first_options' => [
                    'label' => 'Password',
                    'attr' => ['placeholder' => 'Create a password'],
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'Please enter a password']),
                        new Assert\Length([
                            'min' => 8,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            'max' => 4096
                        ]),
                        new Assert\Regex([
                            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/',
                            'message' => 'Must contain: 1 uppercase, 1 lowercase, 1 number, and 1 special character (@$!%*?&#)'
                        ])
                    ]
                ],
                'second_options' => [
                    'label' => 'Repeat Password',
                    'attr' => ['placeholder' => 'Confirm your password']
                ]
            ])
            ->add('genre', ChoiceType::class, [
                'label' => 'Gender',
                'choices' => [
                    'Male' => 'male',
                    'Female' => 'female',
                    'Other' => 'other'
                ],
                'placeholder' => 'Choose your gender',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please select your gender'])
                ],
                'attr' => [
                    'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500'
                ]
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Phone Number',
                'attr' => [
                    'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500',
                    'placeholder' => 'Phone number'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your phone number']),
                    new Assert\Regex([
                        'pattern' => '/^\+?[0-9]{8,15}$/',
                        'message' => 'Please enter a valid phone number (8-15 digits, optional + prefix)'
                    ])
                ]
            ])
            ->add('dateNaissance', DateType::class, [
                'label' => 'Birth Date',
                'widget' => 'single_text',
                'html5' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your birth date']),
                    new Assert\LessThan([
                        'value' => 'today',
                        'message' => 'Birth date cannot be in the future'
                    ]),
                    new Assert\GreaterThan([
                        'value' => '-120 years',
                        'message' => 'Please enter a valid birth date'
                    ])
                ],
                'attr' => [
                    'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500',
                    'max' => (new \DateTime())->format('Y-m-d')
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
                    new Assert\IsTrue(['message' => 'You should agree to our terms.']),
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

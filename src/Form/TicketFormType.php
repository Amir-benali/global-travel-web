<?php

                            namespace App\Form;

                            use App\Entity\Tickets;
                            use App\Entity\User;
                            use App\Entity\Enum\Ticket\TicketClass;
                            use Symfony\Bridge\Doctrine\Form\Type\EntityType;
                            use Symfony\Component\Form\AbstractType;
                            use Symfony\Component\Form\FormBuilderInterface;
                            use Symfony\Component\OptionsResolver\OptionsResolver;
                            use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
                            use Symfony\Component\Form\Extension\Core\Type\EmailType;
                            use Symfony\Component\Form\Extension\Core\Type\NumberType;
                            use App\Repository\UserRepository;

                            class TicketFormType extends AbstractType
                            {
                                private UserRepository $userRepository;

                                public function __construct(UserRepository $userRepository)
                                {
                                    $this->userRepository = $userRepository;
                                }

                                public function buildForm(FormBuilderInterface $builder, array $options): void
                                {
                                    $builder
                                        ->add('seatNumber', ChoiceType::class, [
                                            'choices' => array_combine($options['available_seats'], $options['available_seats']),
                                            'label' => 'Numéro de siège',
                                            'required' => true,
                                        ])
                                        ->add('ticketClass', ChoiceType::class, [
                                            'choices' => array_combine(
                                                array_map(fn($class) => $class->value, TicketClass::cases()), // Utilise les valeurs de l'énumération
                                                array_map(fn($class) => $class->value, TicketClass::cases())  // Associe les valeurs comme clés
                                            ),
                                            'label' => 'Classe du billet',
                                            'required' => true,
                                            'placeholder' => 'Select a class',
                                        ])
                                        ->add('ticketPrice', NumberType::class, [
                                            'label' => 'Prix du billet (€)',
                                            'required' => true,
                                        ])
                                        ->add('passengerEmail', EmailType::class, [
                                            'label' => 'Email du passager',
                                            'required' => true,
                                        ])
                                        ->add('selectedUser', EntityType::class, [
                                            'class' => User::class,
                                            'choices' => $this->userRepository->findByRole('ROLE_EMPLOYEE'),
                                            'choice_label' => 'lastname',
                                            'label' => 'Passager',
                                            'attr'=> [
                                                'id' => 'selectedUser',
                                            ],
                                            'placeholder' => 'Choisissez un passager',
                                            'required' => true,
                                            'attr' => ['id' => 'selectedUser'],
                                            'choice_attr'=>function (User $user) {
                                                return [
                                                    'data-email' => $user->getEmail(),
                                                ];

                                            }
                                        ]);
                                }

                                public function configureOptions(OptionsResolver $resolver): void
                                {
                                    $resolver->setDefaults([
                                        'data_class' => Tickets::class,
                                        'available_seats' => [],
                                    ]);
                                }
                            }
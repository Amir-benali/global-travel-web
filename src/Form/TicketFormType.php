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
use Symfony\Component\Validator\Constraints as Assert;
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
                'label' => 'Seats',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Seat number is required.']),
                ],
            ])
            ->add('ticketClass', ChoiceType::class, [
                'choices' => array_combine(
                    array_map(fn($class) => $class->value, TicketClass::cases()),
                    array_map(fn($class) => $class->value, TicketClass::cases())
                ),
                'label' => 'Ticket class',
                'required' => true,
                'placeholder' => 'Select a class',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Ticket class is required.']),
                ],
            ])
            ->add('ticketPrice', NumberType::class, [
                'label' => 'Ticket price',
                'required' => true,
                'constraints' => [
                    new Assert\NotNull(['message' => 'Ticket price is required.']),
                ],
            ])
            ->add('passengerEmail', EmailType::class, [
                'label' => 'Passenger Email',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Passenger Email is required.']),
                    new Assert\Email(['message' => 'Passenger Email is required.']),
                ],
            ])
            ->add('selectedUser', EntityType::class, [
                'class' => User::class,
                'choices' => $this->userRepository->findByRole('ROLE_EMPLOYEE'),
                'choice_label' => 'lastname',
                'label' => 'Passenger',
                'placeholder' => 'Select a passenger',
                'required' => true,
                'attr' => ['id' => 'selectedUser'],
                'choice_attr' => function (User $user) {
                    return [
                        'data-email' => $user->getEmail(),
                    ];
                },
                'constraints' => [
                    new Assert\NotNull(['message' => 'Passenger is required.']),
                ],
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
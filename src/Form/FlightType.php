<?php

namespace App\Form;

use App\Entity\Airlines;
use App\Entity\Enum\Flight\FlightStatus;
use App\Entity\Flights;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class FlightType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('flightNumber', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le numéro de vol est obligatoire.']),
                    new Assert\Length([
                        'max' => 10,
                        'maxMessage' => 'Le numéro de vol ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('departureAirportName', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom de l\'aéroport de départ est obligatoire.']),
                ],
            ])
            ->add('arrivalAirportName', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom de l\'aéroport d\'arrivée est obligatoire.']),
                ],
            ])

            ->add('departureTime', DateTimeType::class, [
                'widget' => 'single_text' ,
        'attr' => ['id' => 'departureTime'],
        'constraints' => [
            new Assert\NotBlank(['message' => 'L\'heure de départ est obligatoire.']),
        ],
    ])
    ->add('arrivalTime', DateTimeType::class, [
        'widget' => 'single_text',
        'attr' => ['id' => 'arrivalTime'],
        'constraints' => [
            new Assert\NotBlank(['message' => 'L\'heure d\'arrivée est obligatoire.']),
            new Assert\Callback(function ($arrivalTime, ExecutionContextInterface $context) {
                $form = $context->getRoot();
                $departureTime = $form->get('departureTime')->getData();

                if ($departureTime && $arrivalTime && $departureTime >= $arrivalTime) {
                    $context->buildViolation('La date de départ doit être antérieure à la date d\'arrivée.')
                        ->atPath('arrivalTime')
                        ->addViolation();
                }
            }),
        ],
    ])
            ->add('availableSeats', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nombre de sièges disponibles est obligatoire.']),
                    new Assert\GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'Le nombre de sièges disponibles doit être supérieur ou égal à 0.',
                    ]),
                ],
            ])
            ->add('flightBasePrice', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prix de base du vol est obligatoire.']),
                    new Assert\GreaterThan([
                        'value' => 0,
                        'message' => 'Le prix de base doit être supérieur à 0.',
                    ]),
                ],
            ])
            ->add('flightStatus', ChoiceType::class, [
                'choices' => FlightStatus::cases(),
                'choice_label' => fn(FlightStatus $status) => $status->value,
                'choice_value' => fn(?FlightStatus $status) => $status?->value,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le statut du vol est obligatoire.']),
                ],
            ])
            ->add('departureCountry', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le pays de départ est obligatoire.']),
                ],
            ])
            ->add('arrivalCountry', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le pays d\'arrivée est obligatoire.']),
                ],
            ])
            ->add('airlineId', EntityType::class, [
                'class' => Airlines::class,
                'choice_label' => 'airlineName',
                'choice_value' => 'airlineId',
                'placeholder' => 'Sélectionnez une compagnie aérienne',
                'constraints' => [
                    new Assert\NotNull(['message' => 'Veuillez sélectionner une compagnie aérienne.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Flights::class,
        ]);
    }
}
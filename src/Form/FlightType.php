<?php

namespace App\Form;


use App\Entity\Airlines;
use App\Entity\Enum\Flight\FlightStatus;
use App\Entity\Flights;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints as Assert;

class FlightType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


        $airportService = $options['airport_service'];
        $airports=$airportService->fetchAirportNames();
        $airportChoices=array_combine($airports, $airports);

        $builder
            ->add('flightNumber', null, [
                'label' => 'Flight number',
                'required' => true, // Rend le champ obligatoire dans le formulaire
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Flight number is required.',
                    ]),
                ],
                'empty_data' =>"", // Valeur par défaut
            ])
            ->add('departureAirportName', ChoiceType::class, [
                'label' => 'Departure Airport',
                'choices' => $airportChoices,
                'placeholder'=>'Select Departure Airport',
                'required' => true, // Rend le champ obligatoire dans le formulaire
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Departure airport is required.',
                    ]),
                ],
                'empty_data' =>-1, // Valeur par défaut
            ])
            ->add('arrivalAirportName', ChoiceType::class, [
                'label' => 'Arrival Airport',
                'choices' => $airportChoices,
                'placeholder'=>'Select Arrival Airport',
                'required' => true, // Rend le champ obligatoire dans le formulaire
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Arrival airport is required.',
                    ]),
                ],
                'empty_data' =>-1, // Valeur par défaut
            ])
            ->add('departureTime', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Departure date',
                'required' => true, // Rend le champ obligatoire dans le formulaire
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Departure date is required.',
                    ]),
                ],
                'empty_data' =>null, // Valeur par défaut
            ])
            ->add('arrivalTime', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Arrival date',
            ])
            ->add('availableSeats',null, [
                'label' => 'Number of Seats',
                'required' => true, // Rend le champ obligatoire dans le formulaire
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Number of seats is required.',
                    ]),
                ],
                'empty_data' =>-1, // Valeur par défaut
            ])
            ->add('flightBasePrice', null, [
                'label' => 'Base price',
                'required' => true, // Rend le champ obligatoire dans le formulaire
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Base price is required.',
                    ]),
                ],
                'empty_data' =>-1, // Valeur par défaut
            ])
            ->add('flightStatus', ChoiceType::class, [
                'choices' => [
                    'SCHEDULED' => FlightStatus::SCHEDULED,
                    'DELAYED' => FlightStatus::DELAYED,
                    'CANCELLED' => FlightStatus::CANCELLED,
                    'COMPLETED' => FlightStatus::COMPLETED,
                ],
                'choice_label' => function ($choice, $key, $value) {
                    return $key; // Utilise les clés comme labels
                },
                'expanded' => false, // Affiche des boutons radio
                'multiple' => false, // Une seule option sélectionnable
                'placeholder'=>'Select Flight Status',
                'label' => 'Flight status',
                'required' => true, // Rend le champ obligatoire dans le formulaire
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Flight status is required.',
                    ]),
                ],
                'empty_data' =>-1, // Valeur par défaut
            ])
            ->add('departureCountry', null, [
                'label' => 'Departure country',
                'required' => true, // Rend le champ obligatoire dans le formulaire
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Departure country is required.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z\s]+$/',
                        'message' => 'The Departure country must contain only letters.',
                    ]),
                ],
                'empty_data' =>"", // Valeur par défaut
            ])
           ->add('arrivalCountry', null, [
                'label' => 'Arrival country',
                'required' => true, // Rend le champ obligatoire dans le formulaire
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Arrival country is required.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z\s]+$/',
                        'message' => 'The arrival country must contain only letters.',
                    ]),
                ],
                'empty_data' =>"", // Valeur par défaut
            ])
            ->add('airlineId', EntityType::class, [
                'class' => Airlines::class,
                'choice_label' => 'airlineName', // Affiche le nom de la compagnie
                'label' => 'Airline company',
                'placeholder'=>'Select Airline',
                'required' => true, // Rend le champ obligatoire dans le formulaire
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Airline company is required.',
                    ]),
                ],
                'empty_data' =>-1, // Valeur par défaut
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Flights::class,
            'airport_service' => null, // Ajoutez cette ligne pour définir le service d'aéroport
        ]);
    }
}
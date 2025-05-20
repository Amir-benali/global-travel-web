<?php

namespace App\Form;

use App\Entity\Airlines;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AirlinesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('airlineName', TextType::class, [
                'label' => 'Airline name',
                'required' => true, // Rend le champ obligatoire dans le formulaire
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Airline name is required.',
                    ]),
                ],
                'empty_data' =>"", // Valeur par défaut
            ])
            ->add('airlineIataCode', TextType::class, [
                'label' => 'IATA Code',
                'required' => true, // Rend le champ obligatoire dans le formulaire
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'IATA code is required.',
                    ]),
                    new Assert\Length([
                        'max' => 15,
                        'maxMessage' => 'IATA code cannot be longer than {{ limit }} characters.',
                    ]),
                ],
                'empty_data' =>"", // Valeur par défaut
            ])
            ->add('airlineCountry', TextType::class, [
                'label' => 'Country',
                'required' => true, // Rend le champ obligatoire dans le formulaire
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Country is required.',
                    ]),
                    new Assert\Length([
                        'max' => 50,
                        'maxMessage' => 'Country cannot be longer than {{ limit }} characters.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z\s]+$/',
                                'message' => 'The country name must contain only letters.',
                    ]),
                ],
                'empty_data' =>"", // Valeur par défaut

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Airlines::class,
        ]);
    }
}
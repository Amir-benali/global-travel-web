<?php

namespace App\Form;

use App\Entity\Hotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomH', TextType::class, [
                'label' => 'Hotel Name',
                'attr' => ['class' => 'form-control']
            ])
            ->add('paysH', TextType::class, [
                'label' => 'Country',
                'attr' => [
                    'class' => 'form-control country-input',
                    'readonly' => true
                ]
            ])
            ->add('villeH', TextType::class, [
                'label' => 'City',
                'attr' => [
                    'class' => 'form-control city-input',
                    'readonly' => true
                ]
            ])
            ->add('adresseH', TextType::class, [
                'label' => 'Address',
                'attr' => ['class' => 'form-control']
            ])
            ->add('categorieH', IntegerType::class, [
                'label' => 'Category (1-7)',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 7
                ]
            ])
            ->add('servicesH', TextType::class, [
                'label' => 'Services',
                'attr' => ['class' => 'form-control']
            ])
            ->add('coordonneesH', TextType::class, [
                'label' => 'Coordinates',
                'attr' => ['class' => 'form-control']
            ])
            ->add('avisH', TextType::class, [
                'label' => 'Review',
                'attr' => ['class' => 'form-control',
                'help' => 'Enter a translation key (e.g., hotel_review_1). Translations must be defined in YAML files.',]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hotel::class,
        ]);
    }
}
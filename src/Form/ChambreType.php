<?php

namespace App\Form;

use App\Entity\Chambre;
use App\Entity\Hotel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChambreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeChambreH', TextType::class, [
                'label' => 'Type of Chambre',
                'attr' => [
                    'maxlength' => 20,
                    'pattern' => '^[a-zA-Z\s]+$',
                    'title' => 'The type can only contain letters and spaces.',
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                ],
            ])
            ->add('prixNuitH', IntegerType::class, [
                'label' => 'Price per Night',
                'attr' => [
                    'min' => 20,
                    'max' => 300,
                    'title' => 'The price must be between 20 and 300.',
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                ],
            ])
            ->add('dispoH', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Availability Date',
                'required' => false, // Make the field optional
                'attr' => [
                    'min' => (new \DateTime())->format('Y-m-d'), // Ensures the date is greater than today
                    'class' => 'w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                    'placeholder' => 'Select a date (optional)',
                ],
            ])
            ->add('optionH', TextType::class, [
                'label' => 'Options',
                'attr' => [
                    'maxlength' => 30,
                    'minlength' => 3,
                    'title' => 'The options must be between 3 and 30 characters.',
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                ],
            ])
            ->add('hotel', EntityType::class, [
                'class' => Hotel::class,
                'choice_label' => 'nomH',
                'label' => 'Hotel',
                'placeholder' => 'Select a Hotel',
                'attr' => [
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chambre::class,
        ]);
    }
}
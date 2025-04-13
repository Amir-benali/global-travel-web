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
                'attr' => [
                    'maxlength' => 255,
                    'pattern' => '^[\p{L}\s]+$',
                    'title' => 'The hotel name can only contain letters and spaces.',
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                ],
            ])
            ->add('adresseH', TextType::class, [
                'label' => 'Address',
                'attr' => [
                    'maxlength' => 30,
                    'minlength' => 3,
                    'title' => 'The address must be between 3 and 30 characters.',
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                ],
            ])
            ->add('villeH', TextType::class, [
                'label' => 'City',
                'attr' => [
                    'maxlength' => 30,
                    'minlength' => 3,
                    'title' => 'The city must be between 3 and 30 characters.',
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                ],
            ])
            ->add('paysH', TextType::class, [
                'label' => 'Country',
                'attr' => [
                    'maxlength' => 30,
                    'minlength' => 3,
                    'title' => 'The country must be between 3 and 30 characters.',
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                ],
            ])
            ->add('categorieH', IntegerType::class, [
                'label' => 'Category (1-7)',
                'attr' => [
                    'min' => 1,
                    'max' => 7,
                    'title' => 'The category must be between 1 and 7.',
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                ],
            ])
            ->add('servicesH', TextType::class, [
                'label' => 'Services',
                'attr' => [
                    'maxlength' => 20,
                    'minlength' => 3,
                    'title' => 'The services must be between 3 and 20 characters.',
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                ],
            ])
            ->add('coordonneesH', TextType::class, [
                'label' => 'Coordinates',
                'attr' => [
                    'maxlength' => 30,
                    'minlength' => 3,
                    'title' => 'The coordinates must be between 3 and 30 characters.',
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                ],
            ])
            ->add('avisH', TextType::class, [
                'label' => 'Review',
                'attr' => [
                    'maxlength' => 30,
                    'minlength' => 3,
                    'title' => 'The review must be between 3 and 30 characters.',
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Hotel::class]);
    }
}
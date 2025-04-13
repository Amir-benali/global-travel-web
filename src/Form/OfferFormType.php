<?php

namespace App\Form;

use App\Entity\CarOffer;
use App\Entity\CarRoute;
use App\Entity\PrivateCar;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OfferFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description'
            , null, [
                'label' => 'Description',
                'attr' => ['placeholder' => 'Enter description'],
            ])
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'placeholder' => 'Select date and time',
                ],
                'label' => 'Date and Time',
            ])
            ->add('price'
            , null, [
                'label' => 'Price',
                'attr' => ['placeholder' => 'Enter price'],
            ])
            ->add('car', EntityType::class, [
                'class' => PrivateCar::class,
                'choice_label' => 'model',
                'placeholder' => 'Select a car',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Add Car',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->add('reset', ResetType::class, [
                'label' => 'Reset',
                'attr' => ['class' => 'btn btn-secondary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CarOffer::class,
        ]);
    }
}

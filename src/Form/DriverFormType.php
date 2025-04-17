<?php

namespace App\Form;

use App\Entity\CarDriver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DriverFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', null, [
                'label' => 'First Name',
                'attr' => ['placeholder' => 'Enter first name'],
                'empty_data' => '',
            ])
            ->add('lastName', null, [
                'label' => 'Last Name',
                'attr' => ['placeholder' => 'Enter last name'],
                'empty_data' => '',
            ])
            ->add('phone', null, [
                'label' => 'Phone',
                'attr' => ['placeholder' => 'Enter phone number'],
                'empty_data' => '',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Add Driver',
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
            'data_class' => CarDriver::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\CarDriver;
use App\Entity\PrivateCar;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CarFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('brand',null, [
                'label' => 'Brand',
                'attr' => ['placeholder' => 'Enter brand'],
                'empty_data' => '',
            ])
            ->add('model',null, [
                'label' => 'Model',
                'attr' => ['placeholder' => 'Enter model'],
                'empty_data' => '',
            ])
            ->add('numPlace',null, [
                'label' => 'Number of Seats',
                'attr' => ['placeholder' => 'Enter number of seats'],
                'empty_data' => -1,
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'maxSizeMessage' => 'The image is too large. Maximum size is 5MB.',
                        
                    ]),
                ]
            ])
            ->add('idDriver', EntityType::class, [
                'class' => CarDriver::class,
                'required' => false,
                'choice_label' => function (CarDriver $driver) {
                    return $driver->getFirstName() . ' ' . $driver->getLastName() ;
                },
                'placeholder' => 'Select a driver',
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
        // $builder->get('image')
        // ->addModelTransformer(new StringToFileTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PrivateCar::class,
        ]);
    }
    
}

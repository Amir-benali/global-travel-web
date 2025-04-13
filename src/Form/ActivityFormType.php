<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\Enum\Activity\ActivityType;
use App\Entity\Hotel;
use App\Entity\PrivateCar;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('datedebut', null, [
                'widget' => 'single_text',
                'label' => 'Start Date',
                'required' => true,
            ])
            ->add('datefin', null, [
                'widget' => 'single_text',
                'label' => 'End Date',
                'required' => true,
            ])
            ->add('description', null, [
                'required' => true,
                'label' => 'Activity Description',
                'attr' => ['placeholder' => 'Enter activity description']
            ])
            ->add('localisation', null, [
                'required' => true,
                'label' => 'Location',
                'attr' => ['placeholder' => 'Enter location']
            ])
            ->add('prixtotal', null, [
                'required' => true,
                'label' => 'Total Price',
                'attr' => ['placeholder' => 'Enter price']
            ])
            ->add('nomactivity', null, [
                'required' => true,
                'label' => 'Activity Name',
                'attr' => ['placeholder' => 'Enter activity name']
            ])
            ->add('typeactivity', EnumType::class, [
                'class' => ActivityType::class,
                'label' => 'Activity Type',
                'required' => true,
                'choice_label' => function (ActivityType $type) {
                    return $type->value;
                },
                'attr' => ['class' => 'form-control']
            ])
            ->add('joinhotel', EntityType::class, [
                'class' => Hotel::class,
                'label' => 'Hotel',
                'choice_label' => 'nomH',
                'required' => false,
                'placeholder' => 'Select a hotel',
            ])
            ->add('joinvoiture', EntityType::class, [
                'class' => PrivateCar::class,
                'label' => 'Car',
                'choice_label' => 'model',
                'required' => false,
                'placeholder' => 'Select a car',
            ])
            // ->add('user', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'firstname',
            //     'required' => false,
            //     'placeholder' => 'Select a user',
            // ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
            ])
            ->add('cancel', ResetType::class, [
                'label' => 'Cancel',

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
            'cancel_url' => '/activity', // URL par défaut
        ]);
    }
}
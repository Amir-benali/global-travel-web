<?php

namespace App\Form;

use App\Entity\Review;
use App\Entity\Activity;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class ReviewFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('commentaire', TextareaType::class, [
                'empty_data' => '',
                'label' => 'Comment',
                'attr' => ['rows' => 5]
            ])
            ->add('note', IntegerType::class, [
                'label' => 'Rating (1-5)',
                'attr' => ['min' => 1, 'max' => 5]
            ])
            ->add('datereview', DateTimeType::class, [
                'label' => 'Review Date',
                'widget' => 'single_text',
                'data' => new \DateTime(),
            ])
            ->add('activityId', EntityType::class, [
                'label' => 'Activity',
                'class' => Activity::class,
                'choice_label' => 'nomactivity', 
                'required' => true,
            ])
            ->add('userid', EntityType::class, [
                'label' => 'User',
                'class' => User::class,
                'choice_label' => 'email', // or any other user identifier
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit Review',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
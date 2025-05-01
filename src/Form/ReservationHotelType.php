<?php

namespace App\Form;

use App\Entity\ReservationHotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ReservationHotelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('roomType', TextType::class, [
                'label' => 'Room Type',
                'mapped' => false,
                'disabled' => true,
                'data' => $options['room_type'] ?? '',
            ])
            ->add('roomPrice', TextType::class, [
                'label' => 'Price per Night',
                'mapped' => false,
                'disabled' => true,
                'data' => $options['room_price'] ?? '',
            ])
            ->add('dateCheckinH', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Check-In Date',
                'attr' => ['min' => (new \DateTime())->format('Y-m-d')],
            ])
            ->add('dateCheckoutH', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Check-Out Date',
                'attr' => ['min' => (new \DateTime())->modify('+1 day')->format('Y-m-d')],
            ])
            ->add('nombreChambresH', IntegerType::class, [
                'label' => 'Number of Rooms',
                'attr' => ['min' => 1, 'max' => 10],
            ])
            ->add('statutH', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Pending' => 'pending',
                    'Confirmed' => 'confirmed',
                ],
                'data' => 'pending',
            ])
            ->add('moyenPaiementH', ChoiceType::class, [
                'label' => 'Payment Method',
                'choices' => [
                    'Credit Card' => 'credit_card',
                    'PayPal' => 'paypal',
                    'Cash' => 'cash',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationHotel::class,
            'room_type' => null,
            'room_price' => null,
            'constraints' => [
                new Callback(function (ReservationHotel $reservation, ExecutionContextInterface $context) {
                    if ($reservation->getDateCheckoutH() <= $reservation->getDateCheckinH()) {
                        $context->buildViolation('Check-out date must be after check-in date.')
                            ->atPath('dateCheckoutH')
                            ->addViolation();
                    }
                }),
            ],
        ]);

        $resolver->setAllowedTypes('room_type', ['string', 'null']);
        $resolver->setAllowedTypes('room_price', ['string', 'null']);
    }
}
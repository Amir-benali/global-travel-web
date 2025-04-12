<?php

                namespace App\Form;

                use App\Entity\Airlines;
                use Symfony\Component\Form\AbstractType;
                use Symfony\Component\Form\Extension\Core\Type\TextType;
                use Symfony\Component\Form\FormBuilderInterface;
                use Symfony\Component\OptionsResolver\OptionsResolver;

                class AirlinesType extends AbstractType
                {
                    public function buildForm(FormBuilderInterface $builder, array $options): void
                    {
                        $builder
                            ->add('airlineName', TextType::class, [
                                'label' => 'Nom de la compagnie',
                            ])
                            ->add('airlineIataCode', TextType::class, [
                                'label' => 'Code IATA',
                            ])
                            ->add('airlineCountry', TextType::class, [
                                'label' => 'Pays',
                            ]);
                    }

                    public function configureOptions(OptionsResolver $resolver): void
                    {
                        $resolver->setDefaults([
                            'data_class' => Airlines::class,
                        ]);
                    }
                }
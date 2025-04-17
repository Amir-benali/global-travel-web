<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class ForgotPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, [
            'label' => 'Email address',
            'attr' => [
                'placeholder' => 'your.email@example.com',
                // Add dark mode bg
                'class' => 'w-full rounded-md border border-gray-300 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2',
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter your email address',
                ]),
                new Email([
                    'message' => 'Please enter a valid email address',
                ]),
            ],
        ]);
    }
}

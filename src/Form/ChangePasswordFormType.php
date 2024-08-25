<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordFormType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('current', PasswordType::class, [
                'label' => 'profile.current.password',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'profile.new.password',
                'attr' => [
                    'class' => 'js-password-input',
                    'placeholder' => '',
                ],
                'always_empty' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'signup.error.password.blank',
                    ]),
                    new Length(['min' => 8]),
                ]
            ])
        ;
    }
}

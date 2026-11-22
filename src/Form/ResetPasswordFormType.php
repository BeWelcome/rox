<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordFormType extends AbstractType
{
    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'label' => 'password',
                'type' => PasswordType::class,
                'invalid_message' => 'password.must.match',
                'required' => true,
                'first_options' => ['label' => 'password'],
                'second_options' => ['label' => 'password.repeat'],
                // the same as a new password on the account page
                'constraints' => [
                    new NotBlank(message: 'signup.error.password.blank'),
                    new Length(min: 8),
                ],
            ])
        ;
    }
}

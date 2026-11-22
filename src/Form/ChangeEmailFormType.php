<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * The new email address of a member (AccountController::changeEmailAddressRequest), with the member's password when
 * they change it themselves (ask_password); admins and the safety team change it without.
 */
class ChangeEmailFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, [
            'label' => 'account.email.new',
            'help' => 'help.email.change',
            'constraints' => [
                new NotBlank(message: 'signup.error.email.blank'),
                new Email(),
            ],
        ]);
        if ($options['ask_password']) {
            $builder->add('password', PasswordType::class, [
                'label' => 'password',
                'help' => 'account.email.password.help',
                'constraints' => [new NotBlank(message: 'account.email.password.empty')],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['ask_password' => true]);
        $resolver->setAllowedTypes('ask_password', 'bool');
    }
}

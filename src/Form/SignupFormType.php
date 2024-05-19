<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class SignupFormType extends AbstractType
{
    private string $usernamePattern;

    public function __construct(string $usernamePattern)
    {
        $this->usernamePattern = $usernamePattern;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'label.username',
                'attr' => [
                    'class' => 'js-username',
                    'placeholder' => 'placeholder.username',
                ],
                'help' => 'help.username',
                'help_html' => true,
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'signup.error.username',
                    ]),
                    new Regex($this->usernamePattern, 'signup.error.username.pattern'),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'label.email',
                'attr' => [
                    'class' => 'js-email-address',
                    'placeholder' => 'placeholder.email',
                ],
                'help' => 'help.email',
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'signup.error.email.blank',
                    ]),
                    new Email()
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'label.password',
                'attr' => [
                    'class' => 'js-password-input',
                    'placeholder' => 'placeholder.password',
                ],
                'always_empty' => false,
                'help' => 'help.password',
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'signup.error.password.blank',
                    ]),
                    new Length(['min' => 8]),
                ],
            ])
            ->add('terms_privacy', CheckboxType::class, [
                'label' => 'signup.label.terms',
                'label_html' => true,
                'help' => 'help.terms',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'signup.error.terms_privacy'
                    ]),
                ],
            ])
        ;
    }
}

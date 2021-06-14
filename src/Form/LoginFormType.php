<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class LoginFormType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'attr' => [
                    'placeholder' => 'Username',
                ],
                'label' => false,
            ])
            ->add('password', PasswordType::class, [
                'attr' => [
                    'placeholder' => 'Password',
                ],
                'label' => false,
            ])
            ->add('login', SubmitType::class)
            ->setAction('/login_check');
    }
}

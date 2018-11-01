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
     * @param FormBuilderInterface $formBuilder
     * @param array                $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
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

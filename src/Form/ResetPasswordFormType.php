<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;

class ResetPasswordFormType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', RepeatedType::class, [
                'label' => 'password',
                'type' => PasswordType::class,
                'invalid_message' => 'password.must.match',
                'required' => true,
                'first_options' => ['label' => 'password'],
                'second_options' => ['label' => 'password.repeat'],
            ])
        ;
    }
}

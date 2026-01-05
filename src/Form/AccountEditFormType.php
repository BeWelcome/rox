<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class AccountEditFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'label.name',
                'attr' => [
                    'placeholder' => 'placeholder.name',
                ],
                'help' => 'help.name',
                'required' => false,
                'constraints' => [
                    new NotBlank(message: 'error.name'),
                ],
            ])
            ->add('show_name', CheckboxType::class, [
                'label' => 'label.name.show',
                'help' => 'help.name.show',
                'required' => false,
            ])
            ->add('short_name', TextType::class, [
                'label' => 'label.shortname',
                'attr' => [
                    'placeholder' => 'placeholder.shortname',
                ],
                'help' => 'help.shortname',
                'required' => false,
            ])
            ->add('birthdate', DateType::class, [
                'label' => 'label.birthdate',
                'attr' => [
                    'placeholder' => 'placeholder.birthdate',
                    'class' => 'js-datepicker o-input',
                ],
                'help' => 'help.birthdate',
                'widget' => 'single_text',
                'html5' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(message: 'error.birthdate'),
                    new LessThan('-18years'),
                ],
            ])
            ->add('show_age', CheckboxType::class, [
                'label' => 'label.age.show',
                'help' => 'help.age.show',
                'required' => false,
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'label.gender',
                'expanded' => true,
                'multiple' => false,
                'help' => 'help.gender',
                'choices' => [
                    'male' => 'male',
                    'female' => 'female',
                    'other' => 'other',
                ],
                'required' => true,
                'constraints' => [
                    new NotNull(message: 'error.gender'),
                ],
            ])
            ->add('show_gender', CheckboxType::class, [
                'label' => 'label.gender.show',
                'help' => 'help.gender.show',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'label.email',
                'attr' => [
                    'class' => 'js-email-address',
                ],
                'help' => 'help.email',
                'required' => false,
                'constraints' => [
                    new NotBlank(message: 'signup.error.email.blank'),
                    new Email(),
                ],
            ])
        ;
    }
}

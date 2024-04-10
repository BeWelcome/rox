<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class DeleteProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('feedback', CkEditorType::class, [
                'label' => 'profile.delete.feedback',
                'required' => false,
            ])
            ->add('data_retention', CheckboxType::class, [
                'label' => 'profile.delete.cleanup',
                'required' => false,
            ])
        ;
        if (false === $options['loggedIn']) {
            $builder
                ->add('username', TextType::class, [
                    'required' => false,
                    'constraints' => [
                        new NotBlank(),
                    ],
                ])
                ->add('password', PasswordType::class, [
                    'required' => false,
                    'constraints' => [
                        new NotBlank(),
                    ],
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'loggedIn' => false,
            ])
            ->addAllowedTypes('loggedIn', 'bool');
    }
}

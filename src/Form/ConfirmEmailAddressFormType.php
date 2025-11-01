<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConfirmEmailAddressFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('registration_key', TextType::class, [
                'label' => 'label.registration_key',
                'attr' => [
                    'placeholder' => 'placeholder.registration_key',
                ],
                'help' => 'help.registration_key',
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'error.registration_key',
                    ]),
                ],
            ])
        ;
    }
}

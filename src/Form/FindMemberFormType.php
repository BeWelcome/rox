<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class FindMemberFormType extends AbstractType
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
                'label' => 'label.username',
                'attr' => [
                    'minlength' => 4,
                    'placeholder' => 'placeholder.username.part',
                ],
                'constraints' => [
                    'pattern'
                ]
            ])
            ->add('reset_password', SubmitType::class, [
                'label' => 'label.reset.password',
            ]);
    }
}

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FindUserFormType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->add('term', TextType::class, [
                'label' => 'label.username.part',
                'attr' => [
                    'minlength' => 4,
                    'placeholder' => 'placeholder.username.part',
                ],
            ])
            ->add('find_member', SubmitType::class, [
                'label' => 'label.find.member',
            ]);
    }
}

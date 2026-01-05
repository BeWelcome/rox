<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class LanguageLevelsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('language', HiddenType::class)
            ->add('language_levels', CollectionType::class, [
                'entry_type' => LanguageLevelFormType::class,
                'entry_options' => [
                    'label' => false,
                    'error_bubbling' => false,
                ],
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'label' => false,
                'attr' => [
                    'class' => 'form-collection',
                ],
            ])
        ;
    }
}

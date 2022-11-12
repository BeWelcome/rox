<?php

namespace App\Form;


use App\Entity\ProfileNote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileNoteFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $categories = array_merge(['' => ''], $options['categories']);

        $builder
            ->add('categories', TomSelectType::class, [
                'label' => 'profile.note.categories',
                'required' => false,
                'allow_create' => false,
                'use_select' => true,
                'multiple' => true,
                'placeholder' => 'profile.note.select.category',
                'choices' => $categories,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(['categories' => []])
        ;
    }
}

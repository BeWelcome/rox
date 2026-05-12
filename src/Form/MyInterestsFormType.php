<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class MyInterestsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $textareaOptions = [
            'required' => false,
            'attr' => ['class' => 'o-input', 'rows' => 4],
        ];

        $builder
            ->add('hobbies', TextareaType::class, $textareaOptions + [
                'label' => 'profile.hobbies',
                'help' => 'help.profile.hobbies',
            ])
            ->add('books', TextareaType::class, $textareaOptions + [
                'label' => 'profile.books',
                'help' => 'help.profile.books',
            ])
            ->add('music', TextareaType::class, $textareaOptions + [
                'label' => 'profile.music',
                'help' => 'help.profile.music',
            ])
            ->add('movies', TextareaType::class, $textareaOptions + [
                'label' => 'profile.movies',
                'help' => 'help.profile.movies',
            ])
            ->add('organizations', TextareaType::class, $textareaOptions + [
                'label' => 'profile.organizations',
                'help' => 'help.profile.organizations',
            ])
            ->add('language', HiddenType::class)
        ;
    }
}

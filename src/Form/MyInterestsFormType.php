<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class MyInterestsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hobbies', CkEditorType::class, [
                'label' => 'label.profile.hobbies',
                'help' => 'help.profile.hobbies',
                'required' => false,
                'image_upload' => false,
            ])
            ->add('books', CkEditorType::class, [
                'label' => 'label.profile.books',
                'help' => 'help.profile.books',
                'required' => false,
                'image_upload' => false,
            ])
            ->add('music', CkEditorType::class, [
                'label' => 'label.profile.music',
                'help' => 'help.profile.music',
                'required' => false,
                'image_upload' => false,
            ])
            ->add('movies', CkEditorType::class, [
                'label' => 'label.profile.movies',
                'help' => 'help.profile.movies',
                'required' => false,
                'image_upload' => false,
            ])
            ->add('organizations', CkEditorType::class, [
                'label' => 'label.profile.organizations',
                'help' => 'help.profile.organizations',
                'required' => false,
                'image_upload' => false,
            ])
            ->add('language', HiddenType::class)
        ;
    }
}

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
                'label' => 'profile.hobbies',
                'help' => 'help.profile.hobbies',
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'required' => false,
                'image_upload' => false,
            ])
            ->add('books', CkEditorType::class, [
                'label' => 'profile.books',
                'help' => 'help.profile.books',
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'required' => false,
                'image_upload' => false,
            ])
            ->add('music', CkEditorType::class, [
                'label' => 'profile.music',
                'help' => 'help.profile.music',
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'required' => false,
                'image_upload' => false,
            ])
            ->add('movies', CkEditorType::class, [
                'label' => 'profile.movies',
                'help' => 'help.profile.movies',
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'required' => false,
                'image_upload' => false,
            ])
            ->add('organizations', CkEditorType::class, [
                'label' => 'profile.organizations',
                'help' => 'help.profile.organizations',
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'required' => false,
                'image_upload' => false,
            ])
            ->add('language', HiddenType::class)
        ;
    }
}

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class AboutMeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('about_me', CkEditorType::class, [
                'label' => 'profile.about.me',
                'help' => 'help.about_me',
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'required' => false,
            ])
            ->add('occupation', CkEditorType::class, [
                'label' => 'profile.occupation',
                'help' => 'help.occupation',
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'required' => false,
            ])
            ->add('offer_hosts', CkEditorType::class, [
                'label' => 'profile.offer.hosts',
                'help' => 'help.offer.hosts',
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'required' => false,
            ])
            ->add('language', HiddenType::class)
        ;
    }
}

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class AboutMeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('about_me', CkEditorType::class, [
                'label' => 'profile.about.me',
                'required' => false,
                'image_upload' => false,
            ])
            ->add('occupation', TextType::class, [
                'label' => 'profile.occupation',
                'help' => 'help.occupation',
                'required' => false,
            ])
            ->add('offer_hosts', CkEditorType::class, [
                'label' => 'profile.offer.hosts',
                'help' => 'help.offer.hosts',
                'required' => false,
            ])
            ->add('language', HiddenType::class)
        ;
    }
}

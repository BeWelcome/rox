<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class AboutMeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $textareaOptions = [
            'required' => false,
            'attr' => ['class' => 'o-input', 'rows' => 6],
        ];

        $builder
            ->add('about_me', TextareaType::class, $textareaOptions + [
                'label' => 'profile.about.me',
                'help' => 'help.about.me',
            ])
            ->add('occupation', TextareaType::class, $textareaOptions + [
                'label' => 'profile.occupation',
                'help' => 'help.occupation',
                'attr' => ['class' => 'o-input', 'rows' => 2],
            ])
            ->add('offer_hosts', TextareaType::class, $textareaOptions + [
                'label' => 'profile.offer.hosts',
                'help' => 'help.offer.hosts',
            ])
            ->add('language', HiddenType::class)
        ;
    }
}

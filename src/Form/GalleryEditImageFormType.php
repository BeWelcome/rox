<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class GalleryEditImageFormType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'label.gallery.title',
                'attr' => [
                    'placeholder' => 'placeholder.gallery.title',
                ],
            ])
            ->add('description', TextType::class, [
                'label' => 'label.gallery.description',
                'attr' => [
                    'placeholder' => 'placeholder.gallery.description',
                ],
            ])
            ->add('id', HiddenType::class)
            ->add('submit', SubmitType::class)
        ;
    }
}

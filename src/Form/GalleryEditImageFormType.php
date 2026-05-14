<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class GalleryEditImageFormType extends AbstractType
{
    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'label.gallery.title',
                'attr' => [
                    'placeholder' => 'placeholder.gallery.title',
                    'class' => 'p-gallery-edit__title-input',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'label.gallery.description',
                'required' => true,
                'attr' => [
                    'placeholder' => 'placeholder.gallery.description',
                    'rows' => 6,
                    'class' => 'p-gallery-edit__textarea',
                ],
            ])
            ->add('id', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'gallery.image.edit.save',
                'attr' => [
                    'class' => 'o-button o-button--l p-gallery-edit__submit',
                ],
            ])
        ;
    }
}

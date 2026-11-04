<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommunityNewsType extends AbstractType
{
    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'label.admin.communitynews.title',
                'attr' => [
                    'placeholder' => 'enter.title',
                    'maxlength' => 255,
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('text', CkEditorType::class, [
                'label' => 'label.admin.communitynews.text',
                'image_upload' => true,
                'attr' => [
                    'placeholder' => 'placeholder.admin.communitynews.text',
                    // headings, quotes and images (assets/js/roxeditor.js)
                    'data-editor-toolbar' => 'news',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('public', CheckboxType::class, [
                'label' => 'admin.communitynews.visible',
                'required' => false,
            ]);
    }
}

<?php

namespace App\Form;

use Override;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CkEditorType extends TextareaType
{
    public const string EDITOR_TYPE_TEXTAREA = 'textarea';
    public const string EDITOR_TYPE_DECOUPLED = 'decoupled';
    public const string EDITOR_TYPE_INLINE = 'inline';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->setAttribute('async', $options['async']);
        $builder->setAttribute('image_upload', $options['image_upload']);
        $builder->setAttribute('editor_type', $options['editor_type']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'attr' => [
                    'class' => 'form-control',
                ],
                'async' => false,
                'image_upload' => true,
                'placeholder' => '',
                'error_bubbling' => false,
                'editor_type' => self::EDITOR_TYPE_TEXTAREA,
            ])
            ->addAllowedTypes('placeholder', 'string')
            ->addAllowedTypes('async', 'bool')
            ->addAllowedTypes('editor_type', 'string')
            ->setAllowedValues('editor_type', [
                self::EDITOR_TYPE_DECOUPLED,
                self::EDITOR_TYPE_TEXTAREA,
                self::EDITOR_TYPE_INLINE,
            ])
        ;
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);
        $view->vars['async'] = $form->getConfig()->getAttribute('async');
        $view->vars['image_upload'] = $form->getConfig()->getAttribute('image_upload');
        $view->vars['editor_type'] = $form->getConfig()->getAttribute('editor_type');
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'ckeditor';
    }
}

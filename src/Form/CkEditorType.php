<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CkEditorType extends TextareaType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->setAttribute('async', $options['async']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'attr' => [
                    'class' => 'editor form-control',
                ],
                'async' => false,
                'placeholder' => '',
                'error_bubbling' => false,
            ])
            ->addAllowedTypes('placeholder', 'string')
            ->addAllowedTypes('async', 'bool');
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);
        $view->vars['async'] = $form->getConfig()->getAttribute('async');
    }

    public function getBlockPrefix(): string
    {
        return 'ckeditor';
    }
}

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TomSelectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'error_bubbling' => false,
                'multiple' => false,
                'allow_clear' => false,
                'allow_create' => false,
                'use_select' => false,
                'placeholder' => '',
                'choices' => [],
            ])
            ->addAllowedTypes('multiple', 'bool')
            ->addAllowedTypes('allow_clear', 'bool')
            ->addAllowedTypes('use_select', 'bool')
            ->addAllowedTypes('allow_create', 'bool')
            ->addAllowedTypes('placeholder', 'string')
            ->addAllowedTypes('choices', 'array')
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $attr = $view->vars['attr'];
        $class = 'js-tom-select';

        $attr['class'] = $class;

        $view->vars['attr'] = $attr;

        $view->vars['allow_create'] = $options['allow_create'];
        $view->vars['multiple'] = $options['multiple'];
        $view->vars['choices'] = $options['choices'];
        $view->vars['use_select'] = $options['use_select'];
        $view->vars['placeholder'] = $options['placeholder'];
        if ($options['multiple']) {
            $view->vars['full_name'] .= '[]';
        }
    }

    #[\Override]
    public function getParent(): string
    {
        return TextType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'tomselect';
    }
}

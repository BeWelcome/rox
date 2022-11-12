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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
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

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
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

    public function getParent()
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'tomselect';
    }
}

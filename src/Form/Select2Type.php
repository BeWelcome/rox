<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Select2Type extends AbstractType
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
                'inline' => false,
                'searchbox' => false,
                'allow_clear' => false,
            ])
            ->addAllowedTypes('inline', 'bool')
            ->addAllowedTypes('searchbox', 'bool')
            ->addAllowedTypes('allow_clear', 'bool')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $attr = $view->vars['attr'];
        $class = 'select2';
        if (true === $options['inline']) {
            $class .= '-inline';
        }
        $attr['class'] = $class;
        if (true === $options['allow_clear']) {
            $attr['data-allow-clear'] = 'true';
        }
        if (false === $options['searchbox']) {
            $attr['data-minimum-results-for-search'] = '-1';
        }
        if (true === $options['multiple']) {
            // Always show search box when multiple selections are allowed
            unset($attr['data-minimum-results-for-search']);
        }
        $view->vars['attr'] = $attr;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}

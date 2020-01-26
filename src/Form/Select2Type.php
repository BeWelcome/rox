<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
                'searchbox' => true,
            ])
            ->addAllowedTypes('inline', 'bool')
            ->addAllowedTypes('searchbox', 'bool')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $attr = $view->vars['attr'];
        $attr['class'] = 'select2';
        if (true === $options['inline']) {
            $attr['class'] .= '-inline';
        }
        if (false === $options['searchbox']) {
            $attr['data-minimum-results-for-search'] = "-1";
        }
        $view->vars['attr'] = $attr;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}

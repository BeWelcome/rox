<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchHomeLocationFormType extends MinimalSearchFormType
{
    /**
     * @param FormBuilderInterface $formBuilder
     * @param array                $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        parent::buildForm($formBuilder, $options);
        $formBuilder
            ->add('search', HiddenType::class);
    }
}

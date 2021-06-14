<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class MinimalSearchFormType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction('search/members')
            ->add('search_geoname_id', HiddenType::class)
            ->add('search_latitude', HiddenType::class)
            ->add('search_longitude', HiddenType::class);
    }
}

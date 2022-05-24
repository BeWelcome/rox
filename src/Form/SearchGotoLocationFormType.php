<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchGotoLocationFormType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('search', TextType::class, [
            'attr' => [
                'placeholder' => 'Where are you going?',
            ],
            'label' => false,
        ]);

        $builder->setAction('search/members');
        $this->addHiddenFields($builder);
        $this->addButtons($builder);
    }

    private function addHiddenFields(FormBuilderInterface $formBuilder)
    {
        $formBuilder
            ->add('search_geoname_id', HiddenType::class)
            ->add('search_latitude', HiddenType::class)
            ->add('search_longitude', HiddenType::class)
        ;
    }

    private function addButtons(FormBuilderInterface $formBuilder)
    {
        $formBuilder->add('update_map', SubmitType::class);
    }
}

<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class GalleryEditImageFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $formBuilder
     * @param array                $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->add('title', TextType::class, [
                'attr' => [
                    'placeholder' => 'Image Title',
                ],
            ])
            ->add('description', TextType::class, [
                'attr' => [
                    'placeholder' => 'Image Description',
                ],
            ])
            ->add('id', HiddenType::class)
            ->add('submit', SubmitType::class)
        ;
    }
}

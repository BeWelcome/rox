<?php

namespace Rox\Admin\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

class CommunityNewsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $formBuilder
     * @param array $options
     * @return FormInterface
     * @internal param FormFactoryInterface $formFactory
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->add('title', TextType::class, [
                'attr' => [
                    'placeholder' => 'Please enter the title',
                ],
                'label' => false,
            ])
            ->add('text', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'Please enter the description',
                ],
                'label' => false,
            ])
            ->add('public', CheckboxType::class, [
                'label' => 'Shall the community be visible to regular members yet?',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'submit',
            ]);
    }
}

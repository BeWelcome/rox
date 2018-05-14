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
                'label' => 'Enter the title of the community news',
            ])
            ->add('text', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'Please enter the description',
                ],
                'label' => 'This will be the body of the news. You can use all formatting options that TinyMCE offers.',
                'required' => false,
            ])
            ->add('public', CheckboxType::class, [
                'label' => 'Shall the community be visible to regular members yet?',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'submit',
            ]);
    }
}

<?php

namespace AppBundle\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommunityNewsType extends AbstractType
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
                    'placeholder' => 'Please enter the title',
                ],
                'label' => 'Enter the title of the community news',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('text', CKEditorType::class, [
                'config' => [
                    'extraPlugins' => 'confighelper',
                ],
                'plugins' => [
                    'confighelper' => [
                        'path' => '/bundles/app/js/confighelper/',
                        'filename' => 'plugin.js',
                    ],
                ],
                'attr' => [
                    'placeholder' => 'This will be the body of the community news.',
                    'class' => 'mb-1',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
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

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommunityNewsType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->add('title', TextType::class, [
                'label' => 'label.admin.communitynews.title',
                'attr' => [
                    'placeholder' => 'enter.title',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('text', CkEditorType::class, [
                'label' => 'label.admin.communitynews.text',
                'attr' => [
                    'placeholder' => 'placeholder.admin.communitynews.text',
                    'class' => 'editor mb-1',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('public', CheckboxType::class, [
                'label' => 'label.admin.communitynews.public',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'label.submit',
            ]);
    }
}

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CommunityNewsCommentType extends AbstractType
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
                'label' => 'Title',
            ])
            ->add('text', CkEditorType::class, [
                'label' => 'Text',
                'attr' => [
                    'class' => 'editor',
                ],
            ])
            ->add('CommunityNewsCommentCreate', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-primary',
                ],
            ])
            ->setAttribute('novalidate', 'novalidate')
            ->setAttribute('test', 'otto')
        ;
    }
}

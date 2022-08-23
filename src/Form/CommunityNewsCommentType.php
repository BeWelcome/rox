<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommunityNewsCommentType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'label.commnunitynews.comment.title',
                'label_attr'=> [
                    'class'=> 'u-hidden',
                ],
                'attr' => [
                    'class' => 'u-mt-32',
                    'aria-label' => 'label.commnunitynews.comment.title',
                    'placeholder' => 'label.commnunitynews.comment.title',
                ],
            ])
            ->add('text', CkEditorType::class, [
                'label' => 'label.communitynews.comment.text',
                'label_attr'=> [
                    'class'=> 'u-hidden',
                ],
                'attr' => [
                    'class' => 'editor form-control',
                    'aria-label' => 'label.communitynews.comment.text',
                    'placeholder' => 'label.communitynews.comment.text',
                ],
            ])
            ->add('CommunityNewsCommentCreate', SubmitType::class, [
                'label' => 'label.communitynews.comment.create',
                'attr' => [
                    'class' => 'o-button u-mt-8',
                ],
            ])
            ->setAttribute('novalidate', 'novalidate')
            ->setAttribute('test', 'otto')
        ;
    }
}

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class GroupType extends AbstractType
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
            ->add('name', TextType::class, [
                'label' => 'label.group.name',
                'help' => 'group.create.name.hint',
                'attr' => [
                    'placeholder' => 'placeholder.group.name',
                ],
            ])
            ->add('description', CkEditorType::class, [
                'label' => 'label.group.description',
                'help' => 'group.create.description.hint',
                'attr' => [
                    'placeholder' => 'placeholder.group.description',
                    'class' => 'editor mb-1',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'label.group.join.public' => 'Public',
                    'label.group.join.approve' => 'NeedAcceptance',
                    'label.group.join.invite' => 'NeedInvitation',
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'headline.group.join',
            ])
            ->add('membersOnly', ChoiceType::class, [
                'choices' => [
                    'label.group.posts.invisible' => 'Yes',
                    'label.group.posts.visible' => 'No',
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'headline.group.posts',
            ])
            ->add('picture', FileType::class, [
                'label' => 'label.group.picture',
                'help' => 'group.picture.help',
                'required' => false,
                'attr' => [
                    'placeholder' => 'group.choose.group.image',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'label.submit',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ])
        ;
    }
}

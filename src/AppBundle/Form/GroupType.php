<?php

namespace AppBundle\Form;

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
                'attr' => [
                    'placeholder' => 'Please enter a name for the group',
                ],
                'label' => 'Group name',
            ])
            ->add('description', CkEditorType::class, [
                'attr' => [
                    'placeholder' => 'Please provide a meaningful description for the new group.',
                    'class' => 'editor mb-1',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'GroupsJoinPublic' => 'Public',
                    'GroupsJoinApproved' => 'NeedAcceptance',
                    'GroupsJoinInvited' => 'NeedInvitation',
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'GroupsJoinHeading',
            ])
            ->add('membersOnly', ChoiceType::class, [
                'choices' => [
                    'GroupsVisiblePosts' => 'Yes',
                    'GroupsInvisiblePosts' => 'No',
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'GroupsVisiblePostsHeading',
            ])
            ->add('picture', FileType::class, [
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ])
        ;
    }
}

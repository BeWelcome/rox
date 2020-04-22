<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ]);
        // \todo check if there is a better way to do this without compromising translation extraction
        if ($options['allowInvitationOnly'])
        {
            $formBuilder
                ->add('type', ChoiceType::class, [
                    'choices' => [
                        'groupsjoinpublic' => 'Public',
                        'groupsjoinapproved' => 'NeedAcceptance',
                        'groupsjoininvited' => 'NeedInvitation',
                    ],
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'groupspublicstatusheading',
                ]);
        } else {
            $formBuilder
                ->add('type', ChoiceType::class, [
                    'choices' => [
                        'groupsjoinpublic' => 'Public',
                        'groupsjoinapproved' => 'NeedAcceptance',
                    ],
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'groupspublicstatusheading',
r                ]);
        }
        $formBuilder
            ->add('membersOnly', ChoiceType::class, [
                'choices' => [
                    'groupsvisibleposts' => 'Yes',
                    'groupsinvisibleposts' => 'No',
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'groupsvisiblepostsheading',
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

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allowInvitationOnly' => false,
        ]);
    }
}

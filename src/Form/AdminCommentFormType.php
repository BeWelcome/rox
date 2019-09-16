<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AdminCommentFormType extends AbstractType
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
            ->add('markAsChecked', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-sm btn-primary mb-2 mr-sm-2',
                ],
                'label' => 'label.admin.comment.mark.checked',
            ])
            ->add('markAsAbuse', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-sm btn-primary mb-2 mr-sm-2',
                ],
                'label' => 'label.admin.comment.mark.abuse',
            ])
            ->add('moveToNegative', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-sm btn-primary mb-2 mr-sm-2',
                ],
                'label' => 'label.admin.comment.move.negative',
            ])
            ->add('deleteComment', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-sm btn-primary mb-2 mr-sm-2',
                ],
                'label' => 'label.admin.comment.delete',
            ])
        ;
        $formBuilder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Comment $comment */
            $comment = $event->getData();
            $form = $event->getForm();
            if ($comment->getDisplayinpublic()) {
                $form->add('hideComment', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn-primary btn-sm mb-2 mr-sm-2',
                    ],
                    'label' => 'label.admin.comment.hide',
                ]);
            } else {
                $form->add('showComment', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn-primary btn-sm mb-2 mr-sm-2',
                    ],
                    'label' => 'label.admin.comment.show',
                ]);
            }
            if ($comment->getAllowedit()) {
                $form->add('disableEditing', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn-primary btn-sm mb-2 mr-sm-2',
                    ],
                    'label' => 'label.admin.comment.lock',
                ]);
            } else {
                $form->add('allowEditing', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn-primary btn-sm mb-2 mr-sm-2',
                    ],
                    'label' => 'label.admin.comment.mark.editable',
                ]);
            }
        });
    }
}

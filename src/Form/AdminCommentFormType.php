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
            ->add('admin.comment.mark.checked', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-sm btn-primary mb-2 mr-sm-2',
                ],
            ])
            ->add('admin.comment.mark.abuse', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-sm btn-primary mb-2 mr-sm-2',
                ],
            ])
            ->add('admin.comment.move.negative', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-sm btn-primary mb-2 mr-sm-2',
                ],
            ])
            ->add('admin.comment.delete', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-sm btn-primary mb-2 mr-sm-2',
                ],
            ])
        ;
        $formBuilder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Comment $comment */
            $comment = $event->getData();
            $form = $event->getForm();
            if ($comment->getDisplayinpublic()) {
                $form->add('admin.comment.hide', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn-primary btn-sm mb-2 mr-sm-2',
                    ],
                ]);
            } else {
                $form->add('admin.comment.show', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn-primary btn-sm mb-2 mr-sm-2',
                    ],
                ]);
            }
            if ($comment->getAllowedit()) {
                $form->add('admin.comment.lock', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn-primary btn-sm mb-2 mr-sm-2',
                    ],
                ]);
            } else {
                $form->add('admin.comment.mark.editable', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn-primary btn-sm mb-2 mr-sm-2',
                    ],
                ]);
            }
        });
    }
}

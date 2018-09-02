<?php

namespace AppBundle\Form;

use AppBundle\Entity\Comment;
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
            ->add('MarkAsChecked', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-sm btn-primary mb-2 mr-sm-2',
                ],
            ])
            ->add('MarkAsAbuse', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-sm btn-primary mb-2 mr-sm-2',
                ],
            ])
            ->add('MoveToNegative', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-sm btn-primary mb-2 mr-sm-2',
                ],
            ])
            ->add('DeleteComment', SubmitType::class, [
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
                $form->add('hideComment', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn-primary btn-sm mb-2 mr-sm-2',
                    ],
                ]);
            } else {
                $form->add('showComment', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn-primary btn-sm mb-2 mr-sm-2',
                    ],
                ]);
            }
            if ($comment->getAllowedit()) {
                $form->add('disableEditing', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn-primary btn-sm mb-2 mr-sm-2',
                    ],
                ]);
            } else {
                $form->add('allowEditing', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn-primary btn-sm mb-2 mr-sm-2',
                    ],
                ]);
            }
        });
    }
}

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FaqFormType extends AbstractType
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
            ->add('faqCategory', TextType::class, [
                'label' => 'label.admin.faq.category',
                'attr' => [
                    'readonly' => true,
                ],
            ])
            ->add('wordCode', TextType::class, [
                'label' => 'label.admin.faq.translation.id',
            ])
            ->add('question', CkEditorType::class, [
                'async' => true,
                'label' => 'label.admin.faq.question',
                'attr' => [
                    'class' => 'editor',
                ],
            ])
            ->add('answer', CkEditorType::class, [
                'label' => 'label.admin.faq.answer',
                'attr' => [
                    'class' => 'editor',
                ],
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'label.admin.faq.active',
                'required' => false,
            ])
        ;
        $formBuilder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $faq = $event->getData();
            $form = $event->getForm();
            if (empty($faq->wordCode)) {
                $form->add('FaqCreate', SubmitType::class, [
                    'label' => 'label.create',
                    'attr' => [
                        'class' => 'btn-primary',
                    ],
                ]);
            } else {
                $form->add('FaqUpdate', SubmitType::class, [
                    'label' => 'label.update',
                    'attr' => [
                        'class' => 'btn-primary',
                    ],
                ]);
            }
        });
    }
}

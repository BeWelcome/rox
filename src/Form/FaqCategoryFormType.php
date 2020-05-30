<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FaqCategoryFormType extends AbstractType
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
            ->add('wordCode', TextType::class, [
                'label' => 'label.admin.faq.category.translation.id',
                'help' => 'help.admin.faq.category.translation.id',
            ])
            ->add('description', TextType::class, [
                'label' => 'label.admin.faq.category.description',
                'help' => 'help.admin.faq.category.description',
            ])

        ;
        $formBuilder->get('wordCode')
            ->addModelTransformer(new CallbackTransformer(
                function ($wordCode) {
                    if (null === $wordCode) {
                        return '';
                    }
                    if (false === stripos($wordCode, 'faq_cat_')) {
                        throw new TransformationFailedException('error.admin.faq.transform.invalid');
                    }

                    return str_replace('faq_cat_', '', $wordCode);
                },
                function ($strippedWordCode) {
                    if (true === stripos($strippedWordCode, 'faq_cat_')) {
                        throw new TransformationFailedException('error.admin.faq.transform.invalid');
                    }

                    return 'faq_cat_' . $strippedWordCode;
                }
            ));
        $formBuilder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $faqCategory = $event->getData();
            $form = $event->getForm();
            if (!$faqCategory) {
                $form->add('FaqCategoryCreate', SubmitType::class, [
                    'label' => 'label.create',
                    'attr' => [
                        'class' => 'btn-primary',
                    ],
                ]);
            } else {
                $form->add('FaqCategoryUpdate', SubmitType::class, [
                    'label' => 'label.update',
                    'attr' => [
                        'class' => 'btn-primary',
                    ],
                ]);
            }
        });
    }
}

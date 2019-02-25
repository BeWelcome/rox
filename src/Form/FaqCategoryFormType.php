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
                'label' => 'admin.faq.category.wordcode',
                'attr' => [
                    'readonly' => true,
                ],
            ])
            ->add('description', TextType::class, [
                'label' => 'admin.faq.category.description',
                'help_text' => 'admin.faq.category.help',
            ])

        ;
        $formBuilder->get('wordCode')
            ->addModelTransformer(new CallbackTransformer(
                function ($wordCode) {
                    if (null === $wordCode) {
                        return '';
                    }
                    if (false === strpos($wordCode, 'Faq_cat_')) {
                        throw new TransformationFailedException('FaqCategory WordCode doesn\'t start with "Faq_cat_"');
                    }

                    return str_replace('Faq_cat_', '', $wordCode);
                },
                function ($strippedWordCode) {
                    if (true === strpos($strippedWordCode, 'Faq_cat_')) {
                        throw new TransformationFailedException('Stripped WordCode starts with "faq_cat_"');
                    }

                    return 'Faq_cat_'.$strippedWordCode;
                }
            ));
        $formBuilder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $faqCategory = $event->getData();
            $form = $event->getForm();
            if (!$faqCategory) {
                $form->add('FaqCategoryCreate', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn-primary',
                    ],
                ]);
            } else {
                $form->add('FaqCategoryUpdate', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn-primary',
                    ],
                ]);
            }
        });
    }
}

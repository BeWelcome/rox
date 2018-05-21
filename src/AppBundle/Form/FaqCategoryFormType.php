<?php

namespace AppBundle\Form;

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
                'label' => 'FaqCategoryWordCode'
            ])
            ->add('description', TextType::class, [
                'label' => 'FaqCategoryDescription'
            ])
        ;
        $formBuilder->get('wordCode')
            ->addModelTransformer(new CallbackTransformer(
                function ($wordCode) {
                    if ($wordCode === null)
                    {
                        return "";
                    }
                    if (strpos($wordCode,'Faq_cat_') === false)
                    {
                        throw new TransformationFailedException('FaqCategory WordCode doesn\'t start with "Faq_cat_"');
                    }
                    return str_replace('Faq_cat_', '', $wordCode);
                },
                function ($strippedWordCode) {
                    if (strpos($strippedWordCode,'Faq_cat_') === true)
                    {
                        throw new TransformationFailedException('Stripped WordCode starts with "faq_cat_"');
                    }
                    return 'Faq_cat_' . $strippedWordCode;
                }
            )
        );
        $formBuilder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $faqCategory = $event->getData();
            $form = $event->getForm();
            if (!$faqCategory) {
                $form->add('FaqCategoryCreate', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn-primary',
                    ]
                ]);
            } else {
                $form->add('FaqCategoryUpdate', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn-primary',
                    ]
                ]);
            }
        });
    }
}

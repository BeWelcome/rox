<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EditTranslationFormType extends AbstractType
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
                'disabled' => true,
                'label' => 'label.admin.translation.wordcode',
            ])
            ->add('englishText', TextAreaType::class, [
                'disabled' => false,
                'attr' => [
                    'readonly' => true,
                ],
                'label' => 'label.admin.translation.englishtext',
            ])
            ->add('locale', TextType::class, [
                'disabled' => true,
                'label' => 'label.admin.translation.locale',
            ])
            ->add('translatedText', TextAreaType::class, [
                'label' => 'label.admin.translation',
                'required' => true,
            ])
            ->add('update', SubmitType::class, [
                'label' => 'label.update',
            ])
        ;
        $formBuilder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $translationRequest = $event->getData();
            $form = $event->getForm();
            if ('en' === $translationRequest->locale) {
                $form
                    ->add('description', TextAreaType::class, [
                        'label' => 'label.admin.translation.description',
                    ]);
            } else {
                $form
                    ->add('description', TextAreaType::class, [
                        'disabled' => true,
                        'label' => 'label.admin.translation.description',
                    ]);
            }
        });
    }
}

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TranslationFormType extends AbstractType
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
                'label' => 'translation.wordcode',
            ])
            ->add('description', TextAreaType::class, [
                'label' => 'translation.description',
            ])
            ->add('englishText', TextAreaType::class);
        $formBuilder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $translationRequest = $event->getData();
            $form = $event->getForm();
            if ('en' !== $translationRequest->locale) {
                $form->add('locale', TextType::class, [
                        'disabled' => true,
                        'label' => 'translation.locale',
                    ])
                    ->add('translatedText', TextAreaType::class, [
                        'required' => false,
                    ]);
            }
            $form->add('Create', SubmitType::class);
        });
    }
}

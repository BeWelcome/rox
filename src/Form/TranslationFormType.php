<?php

namespace App\Form;

use App\Doctrine\DomainType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TranslationFormType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->add('description', TextAreaType::class, [
                'label' => 'translation.description',
            ])
            ->add('englishText', TextAreaType::class, [
                'label' => 'label.admin.translation.englishtext',
            ])
        ;
        $formBuilder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $translationRequest = $event->getData();
            $form = $event->getForm();
            if ('en' !== $translationRequest->locale) {
                $form->add('locale', TextType::class, [
                    'attr' => [
                        'readonly' => true,
                    ],
                        'label' => 'translation.locale',
                    ])
                    ->add('translatedText', TextAreaType::class, [
                        'required' => false,
                    ])
                ;
            }
            if (null === $translationRequest->wordCode) {
                $form->add('wordCode', TextType::class, [
                        'label' => 'translation.wordcode',
                    ])
                ;
            } else {
                $form->add('wordCode', TextType::class, [
                        'attr' => [
                            'readonly' => true,
                        ],
                        'label' => 'translation.wordcode',
                    ])
                ;
            }
            if (null === $translationRequest->domain || 'en' === $translationRequest->locale) {
                $form
                    ->add('domain', ChoiceType::class, [
                        'label' => 'translation.domain',
                        'choices' => [
                            DomainType::MESSAGES => DomainType::MESSAGES,
                            DomainType::ICU_MESSAGES => DomainType::ICU_MESSAGES,
                            DomainType::VALIDATORS => DomainType::VALIDATORS,
                        ],
                        'choice_translation_domain' => false,
                        'attr' => [
                            'class' => 'select2',
                        ],
                    ])
                ;
            } else {
                $form
                    ->add('domain', TextType::class, [
                        'label' => 'translation.domain',
                        'attr' => [
                            'readonly' => true,
                        ],
                    ])
                ;
            }
            $form->add('create', SubmitType::class);
        });
    }
}

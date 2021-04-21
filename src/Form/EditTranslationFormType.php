<?php

namespace App\Form;

use App\Doctrine\DomainType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditTranslationFormType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->add('wordCode', TextType::class, [
                'disabled' => true,
                'label' => 'label.admin.translation.wordcode',
            ])
            ->add('locale', TextType::class, [
                'disabled' => true,
                'label' => 'label.admin.translation.locale',
            ])
            ->add('update', SubmitType::class, [
                'label' => 'label.update',
            ])
            ->add('englishText', HiddenType::class, [
                'disabled' => false,
                'attr' => [
                    'readonly' => true,
                    'rows' => 10,
                ],
                'label' => 'label.admin.translation.englishtext',
            ])
        ;
        $formBuilder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $richtext = $form->getConfig()->getOption('richtext');
            $translationRequest = $event->getData();
            $wordCode = $translationRequest->wordCode;
            if ($richtext) {
                $fieldType = CkEditorType::class;
            } else {
                $fieldType = TextareaType::class;
            }
            $translatedTextHelp = null;
            if ('en' === $translationRequest->locale) {
                $form
                    ->add('isMajorUpdate', CheckboxType::class, [
                        'label' => 'translation.is.major.update',
                        'required' => false,
                    ])
                    ->add('isArchived', CheckboxType::class, [
                        'label' => 'translation.is.archived',
                        'required' => false,
                    ])
                    ->add('translationAllowed', CheckboxType::class, [
                        'label' => 'translation.allow.translation',
                        'required' => false,
                    ])
                    ->add('description', TextAreaType::class, [
                        'label' => 'label.admin.translation.description',
                    ])
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
                    ->add('description', TextAreaType::class, [
                        'label' => 'label.admin.translation.description',
                        'disabled' => true,
                    ])
                    ->add('domain', TextType::class, [
                        'label' => 'translation.domain',
                        'attr' => [
                            'readonly' => true,
                        ],
                    ])
                ;
                if ($translationRequest->isMajorUpdate) {
                    $translatedTextHelp = 'translation.needs.update';
                }
            }
            $form
                ->add('translatedText', $fieldType, [
                    'attr' => [
                        'rows' => 10,
                        'class' => 'editor',
                    ],
                    'label' => 'label.admin.translation',
                    'required' => true,
                    'help' => $translatedTextHelp,
                ])
            ;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'richtext' => false,
        ]);
    }
}

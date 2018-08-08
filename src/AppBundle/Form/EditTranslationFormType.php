<?php

namespace AppBundle\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
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
                'label' => 'translation.wordcode',
            ])
            ->add('description', TextAreaType::class, [
                'disabled' => true,
                'label' => 'translation.description',
            ])
            ->add('englishText', TextAreaType::class, [
                'disabled' => true,
                'label' => 'translation.englishText',
            ])
            ->add('locale', TextType::class, [
                'disabled' => true,
                'label' => 'translation.locale',
            ])
            ->add('translatedText', TextAreaType::class, [
                'required' => true,
            ])
            ->add('update', SubmitType::class)
        ;
    }
}

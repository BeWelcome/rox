<?php

namespace App\Form;

use App\Doctrine\LanguageLevelType;
use App\Entity\MemberLanguageLevel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LanguageLevelFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('language', LanguageType::class, [
                'spoken' => true,
                'signed' => true,
            ])
            ->add('level', ChoiceType::class, [
                'label' => 'label.language_level',
                'multiple' => false,
                'autocomplete' => false,
                'help' => 'help.language_level',
                'choices' => [
                    'language_level.mother_tongue' => LanguageLevelType::MOTHER_TONGUE,
                    'language_level.fluent' => LanguageLevelType::FLUENT,
                    'language_level.expert' => LanguageLevelType::EXPERT,
                    'language_level.intermediate' => LanguageLevelType::INTERMEDIATE,
                    'language_level.beginner' => LanguageLevelType::BEGINNER,
                ],
                'label_html' => true,
                'error_bubbling' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(message: 'error.language_level'),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MemberLanguageLevel::class,
        ]);
    }
}

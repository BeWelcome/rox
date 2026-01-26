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
                'label' => 'label.language.level',
                'multiple' => false,
                'autocomplete' => false,
                'help' => 'help.language.level',
                'choices' => [
                    'language.level.mother.tongue' => LanguageLevelType::MOTHER_TONGUE,
                    'language.level.fluent' => LanguageLevelType::FLUENT,
                    'language.level.expert' => LanguageLevelType::EXPERT,
                    'language.level.intermediate' => LanguageLevelType::INTERMEDIATE,
                    'language.level.beginner' => LanguageLevelType::BEGINNER,
                ],
                'label_html' => true,
                'error_bubbling' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(message: 'error.language.level'),
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

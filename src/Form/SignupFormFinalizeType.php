<?php

namespace App\Form;

use App\Doctrine\AccommodationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class SignupFormFinalizeType extends AbstractType
{
    /**
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     *
     * \todo Build up form from smaller functions.
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     *
     * Parameter $options not used but signature is given by symfony.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['show_registration_key']) {
            $builder->add('registration_key', TextType::class, [
                'label' => 'label.registration.key.optional',
                'attr' => [
                    'placeholder' => 'placeholder.registration.key.optional',
                ],
                'help' => 'help.registration.key.optional',
                'required' => false,
            ]);
        } else {
            $builder->add('registration_key', HiddenType::class);
        }

        $builder
            ->add('name', TextType::class, [
                'label' => 'label.name',
                'attr' => [
                    'placeholder' => 'placeholder.name',
                ],
                'help' => 'help.name',
                'required' => false,
                'constraints' => [
                    new NotBlank(message: 'error.name'),
                ],
            ])
            ->add('short_name', TextType::class, [
                'label' => 'label.shortname',
                'attr' => [
                    'placeholder' => 'placeholder.shortname',
                ],
                'help' => 'help.shortname',
                'required' => false,
            ])
            ->add('birthdate', DateType::class, [
                'label' => 'label.birthdate',
                'attr' => [
                    'placeholder' => 'placeholder.birthdate',
                    'class' => 'js-datepicker o-input',
                ],
                'help' => 'help.birthdate',
                'widget' => 'single_text',
                'html5' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(message: 'error.birthdate'),
                    new LessThan('-18years'),
                ],
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'label.gender',
                'expanded' => true,
                'multiple' => false,
                'help' => 'help.gender',
                'choices' => [
                    'male' => 'male',
                    'female' => 'female',
                    'other' => 'other',
                ],
                'required' => false,
                'constraints' => [
                    new NotNull(message: 'error.gender'),
                ],
            ])
            ->add('mother_tongue', LanguageType::class, [
                'spoken' => true,
                'signed' => true,
                'multiple' => true,
                'label' => 'label.mother_tongue',
                'help' => 'help.mother_tongue',
                'constraints' => [
                    new NotBlank(message: 'error.mother_tongue'),
                ],
            ])
            ->add('location', SetLocationType::class, [
                'attr' => [
                    'class' => 'js-location-picker',
                ],
                'label' => 'profile.set.location',
                'error_bubbling' => true,
                'help' => 'help.location',
            ])
            ->add('accommodation', ChoiceType::class, [
                'label' => 'label.accommodation',
                'expanded' => true,
                'multiple' => false,
                'help' => 'help.accommodation',
                'choices' => [
                    'accommodation.no' => AccommodationType::NO,
                    'accommodation.yes' => AccommodationType::YES,
                ],
                'required' => false,
                'constraints' => [
                    new NotNull(message: 'error.accommodation'),
                ],
            ])
            ->add('hosting_interest', RangeType::class, [
                'label' => 'label.hosting_interest',
                'help' => 'help.hosting_interest',
                'required' => false,
                'attr' => [
                    'min' => 0,
                    'max' => 10,
                ],
                'data' => 0,
            ])
            ->add('newsletters', CheckboxType::class, [
                'label' => 'signup.label.newsletters',
                'required' => false,
            ])
            ->add('local_events', CheckboxType::class, [
                'label' => 'signup.label.local_events',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'show_registration_key' => true,
        ]);
    }
}

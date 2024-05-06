<?php

namespace App\Form;

use App\Doctrine\AccommodationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Contracts\Translation\TranslatorInterface;

class SignupFormFinalizeType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'label.name',
                'attr' => [
                    'placeholder' => 'placeholder.name',
                ],
                'help' => 'help.name',
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'error.name',
                    ]),
                ],
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
                    new NotBlank([
                        'message' => 'error.birthdate',
                    ]),
                ],
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'label.gender',
                'expanded' => true,
                'multiple' => false,
                'help' => 'help.gender',
                'choices' => [
                    $this->translator->trans('male') => 'male',
                    $this->translator->trans('female') => 'female',
                    $this->translator->trans('other') => 'other',
                ],
                'required' => false,
                'constraints' => [
                    new NotNull([
                        'message' => 'error.gender',
                    ]),
                ],
            ])
            ->add('location', SetLocationType::class, [
                'attr' => [
                    'class' =>  'js-location-picker',
                ],
                'error_bubbling' => true,
                'help' => 'help.location',
            ])
            ->add('accommodation', ChoiceType::class, [
                'label' => 'label.accommodation',
                'expanded' => true,
                'multiple' => false,
                'help' => 'help.accommodation',
                'choices' => [
                    $this->translator->trans('accommodation.no') => AccommodationType::NO,
                    $this->translator->trans('accommodation.yes') => AccommodationType::YES,
                ],
                'required' => false,
                'constraints' => [
                    new NotNull([
                        'message' => 'error.accommodation',
                    ]),
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
            ])
            ->add('newsletters', CheckboxType::class, [
                'label' => 'signup.label.newsletters',
                'required' => false
            ])
            ->add('local_events', CheckboxType::class, [
                'label' => 'signup.label.local_events',
                'required' => false
            ])
            ->add('trips_notifications', ChoiceType::class, [
                'label' => 'label.trips_notifications',
                'help' => 'help.trips_notifications',
                'expanded' => false,
                'multiple' => false,
                'choices' => [
                    $this->translator->trans('trips.never') => 'never',
                    $this->translator->trans('trips.immediately') => 'immediately',
                    $this->translator->trans('trips.daily') => 'daily',
                    $this->translator->trans('trips.weekly') => 'weekly',
                    $this->translator->trans('trips.biweekly') => 'biweekly',
                    $this->translator->trans('trips.monthly') => 'monthly',
                ],
                'required' => false,
            ])
        ;
    }
}

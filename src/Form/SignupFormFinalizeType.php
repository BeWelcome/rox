<?php

namespace App\Form;

use App\Doctrine\AccommodationType;
use App\Entity\Language;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\Translation\TranslatorInterface;

class SignupFormFinalizeType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

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
            // Mother tongue, asked on prod's sign-up as well (one language, spoken and signed languages grouped).
            // Translation keys are prod's; the catalogue holds them in lower case.
            ->add('mother_tongue', EntityType::class, [
                'class' => Language::class,
                'query_builder' => fn (EntityRepository $repository): QueryBuilder => $repository
                    ->createQueryBuilder('l')
                    ->where('l.isspokenlanguage = true OR l.issignlanguage = true')
                    ->orderBy('l.englishname', 'ASC'),
                'choice_label' => fn (Language $language): string => strtolower((string) $language->getWordCode()),
                'choice_translation_domain' => 'messages',
                'group_by' => fn (Language $language): string => $language->getIsSignlanguage()
                    ? 'signedlanguages' : 'spokenlanguages',
                'label' => 'languagelevel_motherlanguage',
                'placeholder' => 'signupselectmothertongue',
                'help' => 'subline_mothertongue',
                'required' => false,
                'constraints' => [
                    new NotNull([
                        'message' => 'signup.error.mothertongue',
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
                    new LessThan('-18years'),
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
                'required' => false,
            ])
            ->add('local_events', CheckboxType::class, [
                'label' => 'signup.label.local_events',
                'required' => false,
            ])
/*            ->add('trips_notifications', ChoiceType::class, [
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
*/
        ;
    }
}

<?php

namespace App\Form;

use App\Entity\ActivityAttendee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * The member's answer to an activity: yes, maybe or no, and a short comment.
 */
class ActivityAttendType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $answers = [
            'activityyesiattend' => ActivityAttendee::ATTENDS_YES,
            'activityimightattend' => ActivityAttendee::ATTENDS_MAYBE,
            'activitysorrycantjoinyou' => ActivityAttendee::ATTENDS_NO,
        ];

        $builder
            ->add('status', ChoiceType::class, [
                'label' => 'activity.headline.join',
                'choices' => $answers,
                'expanded' => true,
                'constraints' => [
                    new NotNull(message: 'activity.answer.empty'),
                    new Choice(choices: array_values($answers)),
                ],
            ])
            ->add('comment', TextType::class, [
                'label' => 'activityyourcomment',
                'required' => false,
                'empty_data' => '',
                'attr' => ['maxlength' => 80],
                'constraints' => [
                    new Length(max: 80),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}

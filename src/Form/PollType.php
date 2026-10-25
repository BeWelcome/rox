<?php

namespace App\Form;

use App\Dto\PollDto;
use App\Entity\Group;
use App\Entity\Poll;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Create and edit a poll. The groups are chosen when the poll is created (option `groups`: the member's
 * groups); the status only when it is edited.
 */
class PollType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'polls_title',
                'attr' => ['maxlength' => 255],
            ])
            ->add('description', CkEditorType::class, [
                'label' => 'poll.description',
                'image_upload' => false,
                'attr' => ['class' => 'editor p-polls-input p-polls-input--box'],
            ])
        ;

        if (null !== $options['groups']) {
            $builder->add('groups', EntityType::class, [
                'label' => 'poll.groups',
                'class' => Group::class,
                'choices' => $options['groups'],
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ]);
        }

        if ($options['edit']) {
            $builder->add('status', ChoiceType::class, [
                'label' => 'poll_status',
                'choices' => [
                    'poll.status.draft' => Poll::STATUS_DRAFT,
                    'poll.status.open' => Poll::STATUS_OPEN,
                    'poll.status.closed' => Poll::STATUS_CLOSED,
                ],
            ]);
        }

        $builder
            ->add('typeOfChoice', ChoiceType::class, [
                'label' => 'poll.type.of.choice',
                'choices' => [
                    'poll.choice.single' => Poll::CHOICE_SINGLE,
                    'poll.choice.multiple' => Poll::CHOICE_MULTIPLE,
                ],
            ])
            ->add('ended', DateTimeType::class, [
                'label' => 'poll.ends',
                'widget' => 'single_text',
                'input' => 'datetime',
            ])
            ->add('resultsVisibility', ChoiceType::class, [
                'label' => 'poll.results.visibility',
                'choices' => [
                    'poll.results.after.vote' => Poll::RESULTS_AFTER_VOTE,
                    'poll.results.visible' => Poll::RESULTS_VISIBLE,
                    'poll.results.hidden' => Poll::RESULTS_HIDDEN,
                ],
            ])
            ->add('anonymous', CheckboxType::class, ['label' => 'poll.anonymous', 'required' => false])
            ->add('allowComment', CheckboxType::class, ['label' => 'poll.allow.comment', 'required' => false])
            ->add('canChangeVote', CheckboxType::class, ['label' => 'poll.can.change.vote', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PollDto::class,
            'groups' => null,
            'edit' => false,
        ]);
        $resolver->setAllowedTypes('groups', ['null', 'array']);
        $resolver->setAllowedTypes('edit', 'bool');
    }
}

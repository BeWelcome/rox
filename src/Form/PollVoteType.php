<?php

namespace App\Form;

use App\Entity\Poll;
use App\Entity\PollChoice;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

/**
 * A vote: one choice (blank votes are allowed, as on the legacy pages) or several, and a comment if the
 * poll allows comments.
 */
class PollVoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Poll $poll */
        $poll = $options['poll'];

        $builder->add('choices', EntityType::class, [
            'label' => 'polls_choice',
            'class' => PollChoice::class,
            'choices' => $poll->getChoices()->toArray(),
            'choice_label' => 'text',
            'multiple' => $poll->isMultipleChoice(),
            'expanded' => true,
            'required' => false,
        ]);

        if ($poll->allowsComments()) {
            $builder->add('comment', TextareaType::class, [
                'label' => 'polls_comment',
                'required' => false,
                'empty_data' => '',
                'constraints' => [new Length(max: 5000)],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('poll');
        $resolver->setAllowedTypes('poll', Poll::class);
    }
}

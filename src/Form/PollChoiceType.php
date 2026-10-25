<?php

namespace App\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PollChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('text', TextType::class, [
            'label' => 'polls_choice',
            'constraints' => [
                new NotBlank(message: 'poll.choice.empty'),
                new Length(max: 1000),
            ],
        ]);
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'poll_choice';
    }
}

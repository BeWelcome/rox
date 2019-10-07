<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpamMessagesIndexFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ids = $options['ids'];
        $builder
            ->add('spamMessages', ChoiceType::class, [
                'choices' => $ids,
                'choice_label' => false,
                'expanded' => true,
                'multiple' => true,
                'label' => 'label.spam.messages',
            ])
            ->add('noSpamMessages', ChoiceType::class, [
                'choices' => $ids,
                'choice_label' => false,
                'expanded' => true,
                'multiple' => true,
                'label' => 'label.no.spam.messages',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'ids' => [],
        ]);
    }
}

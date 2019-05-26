<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageIndexFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $deleteButtonLabel = ('deleted' === $options['folder']) ? 'label.undelete' : 'label.delete';
        $spamButtonLabel = ('spam' === $options['folder']) ? 'label.marknospam' : 'label.markspam';
        $builder
            ->add('delete', SubmitType::class, [
                'label' => $deleteButtonLabel,
            ])
            ->add('spam', SubmitType::class, [
                'label' => $spamButtonLabel,
            ])
            ->add('messages', ChoiceType::class, [
                'label' => 'label.messages',
                'choices' => $options['ids'],
                'choice_label' => false,
                'expanded' => true,
                'multiple' => true,
            ]);
        if ('deleted' === $options['folder']) {
            $builder
                ->add('purge', SubmitType::class, [
                    'label' => 'label.purge',
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'ids' => [],
            'folder' => '',
        ]);
    }
}

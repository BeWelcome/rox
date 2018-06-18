<?php

namespace AppBundle\Form;

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
        $deleteButtonLabel = ('deleted' === $options['folder']) ? 'undelete' : 'delete';
        $spamButtonLabel = ('spam' === $options['folder']) ? 'reportAsNoSpam' : 'reportAsSpam';
        $builder
            ->add('delete', SubmitType::class, [
                'label' => $deleteButtonLabel,
            ])

            ->add('spam', SubmitType::class, [
                'label' => $spamButtonLabel,
            ])
            ->add('messages', ChoiceType::class, [
                'choices' => $options['ids'],
                'expanded' => true,
                'multiple' => true,
            ]);
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

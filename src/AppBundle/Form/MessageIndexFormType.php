<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
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
        $deleteButtonLabel = ($options['folder'] == 'deleted') ? 'undelete' : 'delete';
        $spamButtonLabel = ($options['folder'] == 'spam') ? 'reportAsNoSpam' : 'reportAsSpam';
        $builder
            ->add('delete', SubmitType::class, [
                'label' => $deleteButtonLabel,
            ])

            ->add('spam', SubmitType::class, [
                'label' => $spamButtonLabel,
            ])
            ->add('messages', ChoiceType::class, [
                'choices' => $options['ids'],
                'expanded'  => true,
                'multiple'  => true,
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

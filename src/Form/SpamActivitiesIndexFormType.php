<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpamActivitiesIndexFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ids = $options['ids'];
        $builder
            ->add('spamActivities', ChoiceType::class, [
                'choices' => $ids,
                'choice_label' => false,
                'expanded' => true,
                'multiple' => true,
                'label' => 'label.spam.activities',
            ])
            ;
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

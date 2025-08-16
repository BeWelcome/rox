<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpamCommunityNewsCommentsIndexFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $ids = $options['ids'];
        $builder
            ->add('spamComments', ChoiceType::class, [
                'choices' => $ids,
                'choice_label' => false,
                'expanded' => true,
                'multiple' => true,
                'label' => 'label.spam.communitynews',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'ids' => [],
        ]);
    }
}

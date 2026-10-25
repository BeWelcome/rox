<?php

namespace App\Form;

use App\Dto\ActivityDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Create and edit an activity. The place uses the same autocomplete as the trip legs (SearchLocationType).
 */
class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'activitytitle',
                'attr' => ['maxlength' => 80],
            ])
            ->add('location', SearchLocationType::class, [
                'label' => 'activitylocation',
                'expired' => false,
            ])
            ->add('address', TextType::class, [
                'label' => 'activityaddress',
                'required' => false,
                'attr' => ['maxlength' => 320],
            ])
            ->add('starts', DateTimeType::class, [
                'label' => 'activitystart',
                'widget' => 'single_text',
                'html5' => true,
                'input' => 'datetime',
            ])
            ->add('ends', DateTimeType::class, [
                'label' => 'activityend',
                'widget' => 'single_text',
                'html5' => true,
                'input' => 'datetime',
            ])
            ->add('online', CheckboxType::class, [
                'label' => 'activityonline',
                'required' => false,
            ])
            ->add('description', CkEditorType::class, [
                'label' => 'activitydescription',
                'image_upload' => false,
                'attr' => ['class' => 'editor p-activities-input p-activities-input--box'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ActivityDto::class,
        ]);
    }
}

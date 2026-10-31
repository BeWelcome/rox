<?php

namespace App\Form\Admin;

use App\Entity\Newsletter;
use App\Form\CkEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * A mass mail: its name (the code of its words), type, subject and text in English. The name and the description
 * for the translators can't be changed once the mass mail exists.
 */
class MassmailType extends AbstractType
{
    public const array TYPE_LABELS = [
        Newsletter::REGULAR_NEWSLETTER => 'adminmassmailedittypegeneral',
        Newsletter::SPECIFIC_NEWSLETTER => 'adminmassmailedittypespecific',
        Newsletter::TERMS_OF_USE => 'adminmassmailedittypetermsofuse',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $types = [];
        foreach ($options['types'] as $type) {
            $types[self::TYPE_LABELS[$type] ?? $type] = $type;
        }

        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'admin.massmail.type',
                'choices' => $types,
                'placeholder' => 'adminmassmaileditselecttype',
                'disabled' => $options['edit'] && !$options['change_type'],
                'constraints' => [new NotBlank(message: 'adminmassmailchooseatype')],
            ])
            ->add('name', TextType::class, [
                'label' => 'adminmassmailname',
                'help' => 'admin.massmail.name.help',
                'disabled' => $options['edit'],
                'constraints' => [
                    new NotBlank(message: 'adminmassmailnameempty'),
                    new Regex(pattern: '/^[a-z0-9_.-]+$/i', message: 'admin.massmail.name.invalid'),
                ],
            ])
            ->add('subject', TextType::class, [
                'label' => 'adminmassmailsubject',
                'constraints' => [new NotBlank(message: 'adminmassmailsubjectempty')],
            ])
            ->add('body', CkEditorType::class, [
                'label' => 'adminmassmailbody',
                'help' => 'admin.massmail.body.help',
                'attr' => ['class' => 'editor mb-1'],
                'constraints' => [new NotBlank(message: 'adminmassmailbodyempty')],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'admin.massmail.description',
                'help' => 'admin.massmail.description.help',
                'attr' => ['rows' => 3],
                'disabled' => $options['edit'],
                'constraints' => [new NotBlank(message: 'adminmassmaildescriptionempty')],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['edit'] ? 'adminmassmailupdate' : 'adminmassmailcreate',
                'attr' => ['class' => 'btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'types' => [],
            'edit' => false,
            'change_type' => false,
        ]);
        $resolver->setAllowedTypes('types', 'array');
        $resolver->setAllowedTypes('edit', 'bool');
        $resolver->setAllowedTypes('change_type', 'bool');
    }
}

<?php

namespace App\Form;

use App\Doctrine\CommentQualityType;
use App\Doctrine\CommentRelationsType;
use App\Entity\Comment;
use App\Form\DataTransformer\SetTypeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommentType extends AbstractType
{
    public function __construct(private readonly SetTypeTransformer $setTypeTransformer)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $commentRelationsType = new CommentRelationsType();
        $commentQualityType = new CommentQualityType();
        $commentChoices = array_merge(['comment.select.quality' => ''], $commentQualityType->getChoicesArray());

        $toMember = $options['to_member'];
        $builder
            ->add('quality', ChoiceType::class, [
                'label' => 'commentquality',
                'empty_data' => '',
                'multiple' => false,
                'expanded' => false,
                'required' => false,
                'choices' => $commentChoices,
                'constraints' => [
                    new NotBlank(message: 'comment.quality.empty'),
                ],
            ])
            ->add('relations', ChoiceType::class, [
                'label' => 'commentlength',
                'label_translation_parameters' => [
                    '%s' => $toMember->getUsername(),
                ],
                'multiple' => true,
                'expanded' => true,
                'choices' => $commentRelationsType->getChoicesArray(),
                'choice_translation_parameters' => static function () use ($toMember) {
                    return ['username' => $toMember->getUsername()];
                },
                'constraints' => [
                    new NotBlank(message: 'comment.relations.empty'),
                ],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'label.comment.text',
                // an emptied comment is '' for the entity (which takes no null): the check below says it is missing
                'empty_data' => '',
                'attr' => [
                    'rows' => 6,
                ],
                'constraints' => [
                    new NotBlank(message: 'comment.text.empty'),
                ],
            ])
        ;
        $builder->get('relations')->addModelTransformer($this->setTypeTransformer);

        if ($options['show_comment_guideline']) {
            $builder
                ->add('guidelines', CheckboxType::class, [
                    'label' => 'confirmationcommentguidelines',
                    'label_html' => true,
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank(message: 'comment.guidelines.empty'),
                    ],
                ])
            ;
        }
        if ($options['show_new_experience']) {
            $builder
                ->add('new_experience', CheckboxType::class, [
                    'label' => 'comment.new.experience',
                    'mapped' => false,
                    'required' => false,
                ])
                ->add('checked_experience', HiddenType::class, [
                    'mapped' => false,
                    'required' => false,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => Comment::class,
                'to_member' => null,
                'show_comment_guideline' => false,
                'show_new_experience' => false,
            ])
        ;
    }
}

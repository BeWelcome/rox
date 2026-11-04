<?php

namespace App\Form;

use App\Form\CustomDataClass\CommunityNewsCommentRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A comment on a community news, or a reply (parent, set by the page's script: assets/js/communitynews.js).
 */
class CommunityNewsCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', TextareaType::class, [
                'label' => 'communitynews.comment.label',
                'attr' => ['rows' => 3, 'placeholder' => 'communitynews.comment.placeholder'],
            ])
            ->add('parent', HiddenType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommunityNewsCommentRequest::class,
        ]);
    }
}

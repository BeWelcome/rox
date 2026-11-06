<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FaqCategoryFormType extends AbstractType
{
    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('wordCode', TextType::class, [
                'label' => 'label.admin.faq.category.translation.id',
                // fixed once the FAQ or category exists: its texts are found by it
                'disabled' => $options['edit'],
                'help' => 'help.admin.faq.category.translation.id',
            ])
            ->add('description', TextType::class, [
                'label' => 'label.admin.faq.category.description',
                'help' => 'help.admin.faq.category.description',
            ])

        ;
        $builder->get('wordCode')
            ->addModelTransformer(new CallbackTransformer(
                static function ($wordCode) {
                    if (null === $wordCode) {
                        return '';
                    }
                    if (false === stripos($wordCode, 'faq_cat_')) {
                        throw new TransformationFailedException('error.admin.faq.transform.invalid');
                    }

                    return strtolower(str_ireplace('faq_cat_', '', $wordCode));
                },
                static function ($strippedWordCode) {
                    if (true === stripos($strippedWordCode, 'faq_cat_')) {
                        throw new TransformationFailedException('error.admin.faq.transform.invalid');
                    }

                    return 'faq_cat_' . $strippedWordCode;
                }
            ));
        $builder->add($options['edit'] ? 'FaqCategoryUpdate' : 'FaqCategoryCreate', SubmitType::class, [
            'label' => $options['edit'] ? 'label.update' : 'label.create',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['edit' => false]);
        $resolver->setAllowedTypes('edit', 'bool');
    }
}

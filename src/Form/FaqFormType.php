<?php

namespace App\Form;

use App\Entity\FaqCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class FaqFormType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('faqCategory', EntityType::class, [
                'class' => FaqCategory::class,
                'choice_label' => function (FaqCategory $faqCategory) {
                    return $this->translator->trans(strtolower($faqCategory->getDescription()));
                },
                'label' => 'label.admin.faq.category',
            ])
            ->add('wordCode', TextType::class, [
                'label' => 'label.admin.faq.translation.id',
                // fixed once the FAQ or category exists: its texts are found by it
                'disabled' => $options['edit'],
            ])
            ->add('question', CkEditorType::class, [
                'label' => 'label.admin.faq.question',
                'async' => true,
            ])
            ->add('answer', CkEditorType::class, [
                'label' => 'label.admin.faq.answer',
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'label.admin.faq.active',
                'required' => false,
            ])
        ;
        $builder->add($options['edit'] ? 'FaqUpdate' : 'FaqCreate', SubmitType::class, [
            'label' => $options['edit'] ? 'label.update' : 'label.create',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['edit' => false]);
        $resolver->setAllowedTypes('edit', 'bool');
    }
}

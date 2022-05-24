<?php

namespace App\Form;

use App\Entity\FaqCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class FaqFormType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('faqCategory', EntityType::class, [
                'class' => FaqCategory::class,
                'attr' => [
                    'class' => 'select2',
                ],
                'choice_label' => function (FaqCategory $faqCategory) {
                    return $this->translator->trans(strtolower($faqCategory->getDescription()));
                },
                'label' => 'label.admin.faq.category',
            ])
            ->add('wordCode', TextType::class, [
                'label' => 'label.admin.faq.translation.id',
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $faq = $event->getData();
            $form = $event->getForm();
            if (empty($faq->wordCode)) {
                $form->add('FaqCreate', SubmitType::class, [
                    'label' => 'label.create',
                    'attr' => [
                        'class' => 'btn-primary',
                    ],
                ]);
            } else {
                $form->add('FaqUpdate', SubmitType::class, [
                    'label' => 'label.update',
                    'attr' => [
                        'class' => 'btn-primary',
                    ],
                ]);
            }
        });
    }
}

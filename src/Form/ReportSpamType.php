<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class ReportSpamType extends AbstractType
{
    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('comment', TextareaType::class, [
                'label' => 'conversation.report.comment',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'conversation.report.placeholder',
                ],
                'required' => false,
            ])
            ->add('report', SubmitType::class, [
                'label' => 'conversation.report',
            ])
        ;
    }
}

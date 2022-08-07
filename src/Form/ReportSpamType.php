<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReportSpamType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('comment', TextAreaType::class, [
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

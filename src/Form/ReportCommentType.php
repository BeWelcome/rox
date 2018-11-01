<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class ReportCommentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $formBuilder
     * @param array                $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->add('feedback', TextareaType::class, [
                'label' => 'Feedback on Comment',
                'attr' => [
                    'class' => 'editor',
                ],
            ])
            ->add('SendFeedback', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-primary pull-right',
                ],
            ]);
    }
}

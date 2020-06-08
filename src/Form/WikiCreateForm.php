<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class WikiCreateForm extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->add('wiki_markup', TextAreaType::class, [
                'attr' => [
                    'rows' => 20,
                ],
                'label' => 'wiki.text',
                'help' => 'wiki.help',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'wiki.update',
            ]);
    }
}

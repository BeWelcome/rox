<?php

namespace Rox\Admin\Queries;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class AdminQueryType extends AbstractType
{
    /**
     * @param FormEvent $event
     * @return FormEvent
     */
    public function _addParameterInputs(FormEvent $event) {
        // Get selected query and necessary inputs
        $data = $event->getData();
        if ($data) {
            $form = $event->getForm();
            $parameters = QueriesModel::getQueryParameters($data['query']);
            if (!empty($parameters)) {
                foreach ($parameters as $number => $parameter) {
                    $form->add('param' . $number, TextType::class, [
                        'constraints' => [
                            new NotBlank()
                        ],
                        'label' => $parameter]);
                }
                $form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                    'attr' => [
                        'class' => 'pull-xs-right btn btn-primary'
                    ]
                ]);
            }
        }
        return $event;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $queries = QueriesModel::getQueries();
        $builder
            ->add('query', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                    'choices' => $queries,
                    'choices_as_values' => true,
                    'attr' => [
                        'class' => 'form-control-label select2',
                        'style' => 'width: 100%;'
                    ],
                    'placeholder' => 'Select a query',
                    'label' => 'Select a query'
                ]
            )
            ->addEventListener(FormEvents::POST_SET_DATA,
                [$this, '_addParameterInputs']
            )->addEventListener(FormEvents::PRE_SUBMIT,
                [$this, '_addParameterInputs']
            );
    }

    /**
     * @return string Name of the form
     */
    public function getName() {
        return 'queries';
    }
}
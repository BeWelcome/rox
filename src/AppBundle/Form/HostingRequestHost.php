<?php

namespace AppBundle\Form;

use AppBundle\Entity\Message;
use AppBundle\Entity\Subject;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class HostingRequestHost extends HostingRequestAbstractType
{
    /**
     * {@inheritdoc}
     *
     * Used for a reply of a host.
     *
     * Offers the possibility to change the dates and process tha request (accept, cancel)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', SubjectType::class)
            ->add('request', HostingRequestType::class);
        $this->addMessageTextArea($builder->getForm(), 'Please enter a message for your guest.');
        $builder->get('subject')
            ->addModelTransformer(new CallbackTransformer(
                function (Subject $subject) {
                    // transform the given subject to an integer
                    return $subject->getId();
                },
                function ($subjectId) {
                    // transform the string back to an array
                    return $subjectId;
                }
            ))
        ;
        $builder->add('decline', SubmitType::class);
        $builder->add('tentatively', SubmitType::class);
        $builder->add('accept', SubmitType::class);
        $builder->add('update', SubmitType::class);
    }
}

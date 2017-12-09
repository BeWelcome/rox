<?php

namespace AppBundle\Form;

use AppBundle\Entity\Message;
use AppBundle\Entity\Subject;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class HostingRequestHost extends AbstractType
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
            ->add('request', HostingRequestType::class)
            ->add('message', CKEditorType::class, [
                'config' => [
                    'extraPlugins' => 'confighelper',
                ],
                'plugins' => [
                    'confighelper' => [
                        'path' => '/bundles/app/js/confighelper/',
                        'filename' => 'plugin.js',
                    ],
                ],
                'attr' => [
                    'placeholder' => 'Please leave a message after the beep',
                    'class' => 'mb-1',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
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

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Message::class,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_request';
    }
}

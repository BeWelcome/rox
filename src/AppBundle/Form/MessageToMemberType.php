<?php

namespace AppBundle\Form;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MessageToMemberType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', SubjectType::class)
            ->add('message', CKEditorType::class, [
                'config' => [
                    'extraPlugins' => 'confighelper',
                ],
                'plugins' => [
                    'confighelper' => [
                        'path' => '/bundles/app/js/confighelper/',
                        'filename' => 'plugin.js',
                    ],
                    'clipboard' => [
                        'path' => '/bundles/app/js/clipboard/',
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
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Message',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_message';
    }
}

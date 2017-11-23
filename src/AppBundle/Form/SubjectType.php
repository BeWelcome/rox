<?php

namespace AppBundle\Form;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SubjectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('subject', CKEditorType::class, [
            'config' => [
                'extraPlugins' => 'confighelper',
            ],
            'plugins' => [
                'confighelper' => [
                    'path' => '/bundles/app/js/confighelper/',
                    'filename' => 'plugin.js',
                ],
            ],
            'inline' => true,
            'attr' => [
                'placeholder' => 'Accommodation request',
                'class' => 'subjectbg',
            ],
            'constraints' => [
                new NotBlank(),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Subject',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_subject';
    }
}

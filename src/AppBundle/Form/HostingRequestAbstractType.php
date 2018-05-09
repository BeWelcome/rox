<?php

namespace AppBundle\Form;

use AppBundle\Entity\Message;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class HostingRequestAbstractType extends AbstractType
{
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
        return 'appbundle_message';
    }

    protected function addMessageTextArea(FormInterface $form, $placeholder)
    {
        $form
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
                    'placeholder' => $placeholder,
                    'class' => 'mb-1',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
    }
}

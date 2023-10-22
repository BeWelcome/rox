<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class GalleryUploadForm extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('albums', TextType::class, [
                'label' => 'gallery.upload_to_album',
                'autocomplete' => true,
                'required' => false,
                'autocomplete_choices' => $options['albums'],
                'allow_options_create' => true,
                'max_items' => 1,
            ])
            ->add('files', FileType::class, [
                'label' => 'files',
                // 'multiple' => true,
            ])
            ->add('upload', SubmitType::class, [
                'label' => 'upload',
            ])
            ->add('abort', SubmitType::class, [
                'label' => 'abort',
            ])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'albums' => [],
        ]);
    }
}

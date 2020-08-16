<?php


namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GalleryUploadForm extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->add('albums', Select2Type::class, [
                'label' => 'gallery.upload_to_album',
                'choices' => $options['albums'],
                'choice_translation_domain' => false,
                'searchbox' => false,
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

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'albums' => [],
        ]);
    }
}
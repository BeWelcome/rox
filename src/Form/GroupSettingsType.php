<?php

namespace App\Form;

use App\Form\CustomDataClass\GroupSettingsRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The settings of a group, for its admins: description, who may join, who sees the posts, picture, categories and
 * location.
 */
class GroupSettingsType extends AbstractType
{
    use GroupCategoriesAndPlaceTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $options['locale'];
        $builder
            ->add('description', CkEditorType::class, [
                'label' => 'label.group.description',
                'attr' => ['class' => 'editor mb-1'],
                // an emptied description is refused by the validation (NotBlank), not by a type error
                'empty_data' => '',
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'groupsjoinpublic' => 'Public',
                    'groupsjoinapproved' => 'NeedAcceptance',
                    'groupsjoininvited' => 'NeedInvitation',
                ],
                'expanded' => true,
                'label' => 'groupspublicstatusheading',
            ])
            ->add('visiblePosts', ChoiceType::class, [
                'choices' => [
                    'groupsvisibleposts' => 'Yes',
                    'groupsinvisibleposts' => 'No',
                ],
                'expanded' => true,
                'label' => 'groupsvisiblepostsheading',
            ])
            ->add('picture', FileType::class, [
                'label' => 'label.group.picture',
                'help' => 'group.picture.help',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'save',
                'attr' => ['class' => 'btn btn-primary'],
            ]);

        $this->addCategoriesAndPlace($builder, $locale, 'group_settings_location', $options['with_categories']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GroupSettingsRequest::class,
            'locale' => 'en',
            // GroupVoter::EDIT_CATEGORIES
            'with_categories' => true,
        ]);
        $resolver->setAllowedTypes('locale', 'string');
        $resolver->setAllowedTypes('with_categories', 'bool');
    }

    public function getBlockPrefix(): string
    {
        return 'group_settings';
    }
}

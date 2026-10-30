<?php

namespace App\Form;

use App\Entity\GroupCategory;
use App\Form\CustomDataClass\GroupSettingsRequest;
use App\Repository\GroupCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The settings of a group, for its admins: description, who may join, who sees the posts, picture, categories and
 * location.
 */
class GroupSettingsType extends AbstractType
{
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
            ->add('categories', EntityType::class, [
                'class' => GroupCategory::class,
                'choices' => $this->getCategoryRepository()->findAllSortedByName($locale),
                'choice_label' => static fn (GroupCategory $category): string => $category->getName($locale),
                'choice_translation_domain' => false,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'group.search.category',
            ])
            ->add('locationName', TextType::class, [
                'label' => 'location',
                'required' => false,
                'attr' => [
                    'class' => 'js-location-picker',
                    'autocomplete' => 'off',
                    'data-location-target' => '#group_settings_location',
                ],
            ])
            ->add('location', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'save',
                'attr' => ['class' => 'btn btn-primary'],
            ]);

        $builder->get('location')->addModelTransformer(new CallbackTransformer(
            static fn (?int $geonameId): string => null === $geonameId ? '' : (string) $geonameId,
            static fn (?string $geonameId): ?int => ctype_digit((string) $geonameId) ? (int) $geonameId : null,
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GroupSettingsRequest::class,
            'locale' => 'en',
        ]);
        $resolver->setAllowedTypes('locale', 'string');
    }

    public function getBlockPrefix(): string
    {
        return 'group_settings';
    }

    private function getCategoryRepository(): GroupCategoryRepository
    {
        /** @var GroupCategoryRepository $repository */
        $repository = $this->entityManager->getRepository(GroupCategory::class);

        return $repository;
    }
}

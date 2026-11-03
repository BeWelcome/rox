<?php

namespace App\Form;

use App\Entity\GroupCategory;
use App\Repository\GroupCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * The fields a new group and a group's settings share: its categories (up to three, shown as pills by
 * group/_categories_field.html.twig) and its place (group/_place_field.html.twig), a geonames id picked by name.
 *
 * @property EntityManagerInterface $entityManager
 */
trait GroupCategoriesAndPlaceTrait
{
    /**
     * @param string $locationId the id of the hidden location field, for the location picker
     */
    private function addCategoriesAndPlace(FormBuilderInterface $builder, string $locale, string $locationId, bool $withCategories): void
    {
        if ($withCategories) {
            /** @var GroupCategoryRepository $categories */
            $categories = $this->entityManager->getRepository(GroupCategory::class);
            $builder->add('categories', EntityType::class, [
                'class' => GroupCategory::class,
                'choices' => $categories->findAllSortedByName($locale),
                'choice_label' => static fn (GroupCategory $category): string => $category->getName($locale),
                'choice_attr' => static fn (GroupCategory $category): array => ['data-icon' => $category->getIcon()],
                'choice_translation_domain' => false,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'group.categories.label',
            ]);
        }

        $builder
            ->add('locationName', TextType::class, [
                'label' => 'group.place.where',
                'required' => false,
                'attr' => [
                    'class' => 'js-location-picker',
                    'autocomplete' => 'off',
                    'data-location-target' => '#' . $locationId,
                ],
            ])
            ->add('location', HiddenType::class);

        $builder->get('location')->addModelTransformer(new CallbackTransformer(
            static fn (?int $geonameId): string => null === $geonameId ? '' : (string) $geonameId,
            static fn (?string $geonameId): ?int => ctype_digit((string) $geonameId) ? (int) $geonameId : null,
        ));
    }
}

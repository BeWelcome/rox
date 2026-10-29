<?php

namespace App\Form\CustomDataClass;

use App\Doctrine\GroupType;
use App\Entity\Group;
use App\Entity\GroupCategory;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The settings a group's admins can change (the name only the group admin team can change).
 */
class GroupSettingsRequest
{
    /** The description in the admin's language. */
    #[Assert\NotBlank]
    public string $description = '';

    #[Assert\Choice(choices: [GroupType::PUBLIC, GroupType::NEED_ACCEPTANCE, GroupType::INVITE_ONLY])]
    public string $type = GroupType::PUBLIC;

    /** The group's forum posts are visible to all members ('Yes') or to the group's members only ('No'). */
    #[Assert\Choice(choices: ['Yes', 'No'])]
    public string $visiblePosts = 'Yes';

    /** A new picture, if any. */
    #[Assert\Image(minWidth: 200, maxWidth: 400, minHeight: 200, maxHeight: 400)]
    public ?File $picture = null;

    /** @var list<GroupCategory> */
    public array $categories = [];

    /** A geonames id: a place, region or country. */
    public ?int $location = null;

    public ?string $locationName = null;

    public static function fromGroup(Group $group, string $locale): self
    {
        $settings = new self();
        $settings->description = $group->getDescription($locale);
        $settings->type = $group->getType();
        $settings->visiblePosts = $group->isMembersOnly() ? 'No' : 'Yes';
        $settings->categories = $group->getCategories();
        $location = $group->getLocation();
        if (null !== $location) {
            $settings->location = $location->getGeonameId();
            $parts = array_unique(array_filter([$location->getName(), $location->getAdmin1()?->getName(), $location->getCountry()?->getName()]));
            $settings->locationName = implode(', ', $parts);
        }

        return $settings;
    }
}

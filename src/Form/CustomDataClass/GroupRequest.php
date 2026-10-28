<?php

namespace App\Form\CustomDataClass;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class GroupRequest
{
    #[Assert\NotBlank]
    public string $name;

    #[Assert\NotBlank]
    public string $description;

    #[Assert\NotBlank]
    public string $type = 'Public';

    /** The group's forum posts are visible to all members ('Yes') or to the group's members only ('No'). */
    #[Assert\Choice(choices: ['Yes', 'No'])]
    public string $visiblePosts = 'Yes';

    #[Assert\Image(minWidth: 200, maxWidth: 400, minHeight: 200, maxHeight: 400)]
    public ?File $picture = null;

    public bool $comments = false;
}

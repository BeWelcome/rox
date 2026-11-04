<?php

namespace App\Form\CustomDataClass\Tools;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Change username of the volunteer tools: both names as a member's username can be (4 to 20 letters, digits and
 * -._, starting with a letter), and different.
 */
class ChangeUsernameRequest
{
    private const string PATTERN = '/^[a-z](?!.*[-_.][-_.])[a-z0-9-._]{2,18}[a-z0-9]$/i';

    #[Assert\NotBlank(message: 'admin.tools.change_username.error.empty')]
    #[Assert\Regex(pattern: self::PATTERN, message: 'admin.tools.change_username.error.invalid')]
    public ?string $oldUsername = null;

    #[Assert\NotBlank(message: 'admin.tools.change_username.error.empty')]
    #[Assert\Regex(pattern: self::PATTERN, message: 'admin.tools.change_username.error.invalid')]
    #[Assert\NotIdenticalTo(propertyPath: 'oldUsername', message: 'admin.tools.change_username.error.same')]
    public ?string $newUsername = null;
}

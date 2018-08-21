<?php
/**
 * Created by PhpStorm.
 * User: raymund
 * Date: 15.08.2018
 * Time: 11:25.
 */

namespace AppBundle\Form\CustomDataClass\Tools;

use Symfony\Component\Validator\Constraints as Assert;

class ChangeUsernameRequest
{
    /**
     * @Assert\Regex(
     *     pattern = "/[a-z](?!.*[-_.][-_.])[a-z0-9-._]{2,18}[a-z0-9]/i",
     *     htmlPattern = "/[A-Za-z](?!.*[-_.][-_.])[A-Za-z0-9-._]{2,18}[A-Za-z0-9]/"
     * )
     *
     * @var string
     */
    public $oldUsername;

    /**
     * @Assert\Regex(
     *     pattern = "/[a-z](?!.*[-_.][-_.])[a-z0-9-._]{2,18}[a-z0-9]/i",
     *     htmlPattern = "/[A-Za-z](?!.*[-_.][-_.])[A-Za-z0-9-._]{2,18}[A-Za-z0-9]/"
     * )
     * @Assert\NotIdenticalTo(
     *     propertyPath = "oldUsername",
     *     message = "New username must be different." )
     *
     * @var string
     */
    public $newUsername;
}

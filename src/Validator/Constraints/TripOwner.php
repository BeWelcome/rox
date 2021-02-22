<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
class TripOwner extends Constraint
{
    public $message = 'This value is not valid.';
}

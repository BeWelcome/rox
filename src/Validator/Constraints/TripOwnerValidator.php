<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Trip;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class TripOwnerValidator extends ConstraintValidator
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TripOwner) {
            throw new UnexpectedTypeException($constraint, TripOwner::class);
        }

        $user = $this->security->getUser();
        if (!$user || !$value instanceof Trip) {
            return;
        }

        if ($user !== $value->getCreator()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}

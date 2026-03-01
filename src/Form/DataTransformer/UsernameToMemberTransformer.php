<?php

namespace App\Form\DataTransformer;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Proxy\Proxy;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class UsernameToMemberTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Transforms an object (member) to a string (username).
     *
     * @param  Member|null $member
     */
    public function transform($member): string
    {
        if (null === $member) {
            return '';
        }

        if ($member instanceof Proxy && !$member->__isInitialized()) {
            try {
                $id = $member->getId();
                $result = $this->entityManager->createQueryBuilder()
                    ->select('m.username')
                    ->from(Member::class, 'm')
                    ->where('m.id = :id')
                    ->setParameter('id', $id)
                    ->getQuery()
                    ->getOneOrNullResult();
                
                if ($result) {
                    return $result['username'];
                }
            } catch (\Exception $e) {
                // Fallback to standard behavior
            }
        }

        return $member->getUsername();
    }

    /**
     * Transforms a string (username) to an object (member).
     *
     * @param  string $username
     * @throws TransformationFailedException if object (member) is not found.
     */
    public function reverseTransform($username): ?Member
    {
        if (!$username) {
            return null;
        }

        $member = $this->entityManager
            ->getRepository(Member::class)
            ->findOneBy(['username' => $username])
        ;

        if (null === $member) {
            throw new TransformationFailedException(sprintf(
                'A member with username "%s" does not exist!',
                $username
            ));
        }

        return $member;
    }
}

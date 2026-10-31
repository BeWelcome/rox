<?php

namespace App\Tests\Repository;

use App\Entity\Friend;
use App\Entity\Member;
use App\Repository\FriendRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Only confirmed friendships count and show; pending ones are listed for the two members only.
 */
#[Group('integration')]
final class FriendRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private FriendRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = static::getContainer()->get(FriendRepository::class);
    }

    public function testOnlyConfirmedFriendshipsCountForBothSides(): void
    {
        [$six, $seven, $eight, $nine] = $this->members('member-6', 'member-7', 'member-8', 'member-9');
        $sixBefore = $this->repository->countFriends($six);
        $sevenBefore = $this->repository->countFriends($seven);

        $confirmed = $this->save(Friend::request($six, $seven)->confirm());
        $this->save(Friend::request($eight, $six));
        $this->save(Friend::request($six, $nine));

        self::assertSame($sixBefore + 1, $this->repository->countFriends($six));
        self::assertSame($sevenBefore + 1, $this->repository->countFriends($seven));
        self::assertContains($confirmed, $this->repository->findFriendsFor($six));
        self::assertContains($confirmed, $this->repository->findFriendsFor($seven));
        self::assertSame($sixBefore + 1, $this->repository->getFriends($six, 1)->getNbResults());
    }

    public function testPendingFriendshipsAreRequestsForOneSideAndSentForTheOther(): void
    {
        [$six, $eight] = $this->members('member-6', 'member-8');
        $request = $this->save(Friend::request($eight, $six));

        self::assertContains($request, $this->repository->findRequestsFor($six));
        self::assertNotContains($request, $this->repository->findSentBy($six));
        self::assertContains($request, $this->repository->findSentBy($eight));
        self::assertNotContains($request, $this->repository->findRequestsFor($eight));
        self::assertNotContains($request, $this->repository->findFriendsFor($six));

        $request->confirm();
        $this->entityManager->flush();
        self::assertNotContains($request, $this->repository->findRequestsFor($six));
        self::assertNotContains($request, $this->repository->findSentBy($eight));
        self::assertContains($request, $this->repository->findFriendsFor($six));
    }

    public function testTheFriendshipBetweenTwoMembersIsFoundFromEitherSide(): void
    {
        [$six, $seven] = $this->members('member-6', 'member-7');
        $friend = $this->save(Friend::request($seven, $six));

        self::assertSame($friend, $this->repository->findFriendshipBetween($six, $seven));
        self::assertSame($friend, $this->repository->findFriendshipBetween($seven, $six));
    }

    public function testThePagesHoldWholeRowsOfSix(): void
    {
        [$six] = $this->members('member-6');

        foreach ([5 => 6, 6 => 6, 10 => 12, 20 => 24, 50 => 54, 100 => 102] as $itemsPerPage => $expected) {
            self::assertSame($expected, $this->repository->getFriends($six, 1, $itemsPerPage)->getMaxPerPage());
        }
    }

    private function save(Friend $friend): Friend
    {
        $this->entityManager->persist($friend);
        $this->entityManager->flush();

        return $friend;
    }

    /**
     * @return Member[]
     */
    private function members(string ...$usernames): array
    {
        return array_map(function (string $username): Member {
            $member = $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
            self::assertInstanceOf(Member::class, $member);

            return $member;
        }, $usernames);
    }
}

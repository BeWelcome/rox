<?php

namespace App\Tests\Entity;

use App\Entity\Group;
use App\Entity\Member;
use App\Entity\RelatedGroup;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group as TestGroup;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[TestGroup('integration')]
class RelatedGroupTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->getConnection()->rollBack();
        parent::tearDown();
    }

    public function testLinkIsStoredAndRemovalIsRecorded(): void
    {
        [$group, $other] = $this->entityManager->getRepository(Group::class)->findBy([], ['id' => 'ASC'], 2);
        $member = $this->entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-1']);

        $link = new RelatedGroup($group, $other, $member);
        $this->entityManager->persist($link);
        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var RelatedGroup $stored */
        $stored = $this->entityManager->find(RelatedGroup::class, $link->getId());
        self::assertSame($group->getId(), $stored->getGroup()->getId());
        self::assertSame($other->getId(), $stored->getRelatedGroup()->getId());
        self::assertSame($member->getId(), $stored->getAddedBy()->getId());
        self::assertFalse($stored->isRemoved());

        $stored->remove($this->entityManager->getReference(Member::class, $member->getId()));
        $this->entityManager->flush();
        $this->entityManager->clear();

        // deletedby IS NULL means linked (RelatedGroupController and the log rely on it)
        $deletedBy = $this->entityManager->getConnection()->fetchOne(
            'SELECT deletedby FROM groups_related WHERE id = ?',
            [$link->getId()],
        );
        self::assertSame($member->getId(), (int) $deletedBy);
        self::assertTrue($this->entityManager->find(RelatedGroup::class, $link->getId())->isRemoved());
    }

    public function testDeletingAGroupRemovesItsLinks(): void
    {
        $connection = $this->entityManager->getConnection();
        // groups without forum threads: a group with threads can't be deleted this way (GroupModel::delete does it)
        $ids = $connection->fetchFirstColumn(
            'SELECT g.id FROM `group` g WHERE NOT EXISTS (SELECT 1 FROM forum_thread t WHERE t.IdGroup = g.id) ORDER BY g.id LIMIT 2'
        );
        self::assertCount(2, $ids, 'the fixtures contain two groups without forum threads');
        [$group, $other] = array_map(fn ($id) => $this->entityManager->find(Group::class, $id), $ids);
        $this->entityManager->persist(new RelatedGroup($group, $other, null));
        $this->entityManager->persist(new RelatedGroup($other, $group, null));
        $this->entityManager->flush();

        // the memberships go with the group (ON DELETE CASCADE)
        $connection->executeStatement('DELETE FROM `group` WHERE id = ?', [$other->getId()]);

        self::assertSame(0, (int) $connection->fetchOne(
            'SELECT COUNT(*) FROM groups_related WHERE ? IN (group_id, related_id)',
            [$other->getId()],
        ));
    }
}

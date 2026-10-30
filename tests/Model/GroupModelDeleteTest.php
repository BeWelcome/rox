<?php

namespace App\Tests\Model;

use App\Entity\Group;
use App\Model\GroupModel;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use PHPUnit\Framework\Attributes\Group as TestGroup;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[TestGroup('integration')]
final class GroupModelDeleteTest extends KernelTestCase
{
    public function testTheBeWelcomeGroupCantBeDeleted(): void
    {
        self::bootKernel();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $beWelcome = $entityManager->find(Group::class, Group::BEWELCOME);
        self::assertInstanceOf(Group::class, $beWelcome);

        try {
            static::getContainer()->get(GroupModel::class)->delete($beWelcome);
            self::fail('The BeWelcome group was deleted.');
        } catch (LogicException) {
        }

        self::assertSame(
            1,
            (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM `group` WHERE id = 0'),
        );
    }
}

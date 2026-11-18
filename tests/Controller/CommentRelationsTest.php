<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * A comment shows how its author knows the member, as ticked on the comment form.
 */
#[Group('integration')]
final class CommentRelationsTest extends WebTestCase
{
    public function testTheCommentsShowHowTheAuthorKnowsTheMember(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
        self::assertInstanceOf(Member::class, $member);
        $entityManager->getConnection()->executeStatement(
            "UPDATE comment SET relations = 'hosted_me,chatted' WHERE to_member_id = ?",
            [$member->getId()],
        );

        $client->loginUser($member);
        $crawler = $client->request('GET', '/members/member-2/comments');

        self::assertResponseIsSuccessful();
        // the test environment shows translation keys; hosting is said from the reader's side
        $relations = $crawler->filter('.p-cmt__relation')->each(static fn ($relation): string => trim($relation->text()));
        self::assertContains('comment.relation.shown.hosted_me', $relations);
        self::assertContains('profile.comment.relation.chatted', $relations);
    }
}

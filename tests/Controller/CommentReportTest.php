<?php

namespace App\Tests\Controller;

use App\Doctrine\CommentAdminActionType;
use App\Doctrine\CommentQualityType;
use App\Entity\Comment;
use App\Entity\Member;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Reporting a comment marks it for the safety team and brings the member back to the comments tab of their profile.
 */
#[Group('integration')]
class CommentReportTest extends WebTestCase
{
    public function testReportingACommentLeadsToTheCommentsTab(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $members = $entityManager->getRepository(Member::class);
        $receiver = $members->findOneBy(['username' => 'member-2']);
        $writer = $members->findOneBy(['username' => 'member-5']);
        $comment = new Comment()
            ->setToMember($receiver)
            ->setFromMember($writer)
            ->setQuality(CommentQualityType::NEGATIVE)
            ->setComment('zztest comment')
            ->setCreated(new DateTime());
        $entityManager->persist($comment);
        $entityManager->flush();

        $client->loginUser($receiver);
        $crawler = $client->request('GET', '/members/member-2/comment/member-5/report');
        $form = $crawler->selectButton('label.send.feedback')->form();
        $form['report_comment[feedback]'] = 'zztest this is not true';
        $client->submit($form);

        $this->assertResponseRedirects('/members/member-2/comments');
        $reported = $entityManager->getRepository(Comment::class)->findOneBy(['toMember' => $receiver, 'fromMember' => $writer]);
        self::assertSame(CommentAdminActionType::ADMIN_CHECK, $reported->getAdminAction());
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }
}

<?php

namespace App\Tests\Controller;

use App\Doctrine\CommentQualityType;
use App\Entity\Comment;
use App\Entity\Member;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The comment cards on the report comment page and on the volunteers' comment page show the comments; Twig fails
 * here (strict_variables) on fields a comment doesn't have.
 */
#[Group('integration')]
class CommentCardsTest extends WebTestCase
{
    public function testTheReceiverSeesTheCommentWhenReportingIt(): void
    {
        $client = static::createClient();
        [$receiver, $writer] = $this->comments();

        $client->loginUser($receiver);
        $client->request('GET', '/members/' . $receiver->getUsername() . '/comment/' . $writer->getUsername() . '/report');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('main', 'zztest comment');
    }

    public function testTheVolunteersSeeTheCommentAndTheReply(): void
    {
        $client = static::createClient();
        [$receiver, $writer] = $this->comments();

        // bwadmin has the Comments right in the fixtures
        $client->loginUser($this->member('bwadmin'));
        $client->request('GET', '/admin/comment/' . $receiver->getUsername() . '/' . $writer->getUsername());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('main', 'zztest comment');
        $this->assertSelectorTextContains('main', 'zztest reply');
    }

    /**
     * @return array{Member, Member} the receiver of the comment and its writer, who got a reply
     */
    private function comments(): array
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $receiver = $this->member('member-2');
        $writer = $this->member('member-5');
        foreach ([[$receiver, $writer, 'zztest comment'], [$writer, $receiver, 'zztest reply']] as [$to, $from, $text]) {
            $entityManager->persist(new Comment()
                ->setToMember($to)
                ->setFromMember($from)
                ->setQuality(CommentQualityType::POSITIVE)
                ->setComment($text)
                ->setCreated(new DateTime()));
        }
        $entityManager->flush();

        return [$receiver, $writer];
    }

    private function member(string $username): Member
    {
        return static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
    }
}

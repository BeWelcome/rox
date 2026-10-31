<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Legacy member URLs whose pages are in src now.
 */
#[Group('integration')]
final class LegacyMemberUrlsTest extends WebTestCase
{
    public function testMyNotesGoToTheNotesOfTheMember(): void
    {
        $client = static::createClient();
        $client->loginUser($this->member('member-2'));

        $client->request('GET', '/mynotes');

        self::assertResponseRedirects('/members/member-2/notes', 301);
    }

    public function testTheProfileOfATripLegGoesToTheProfile(): void
    {
        $client = static::createClient();
        $client->loginUser($this->member('member-2'));

        $client->request('GET', '/members/member-4/leg/5');

        self::assertResponseRedirects('/members/member-4', 301);
    }

    public function testTheOldCommentRelationAndVisitorUrlsLeadToTheNewOnes(): void
    {
        $client = static::createClient();
        $client->loginUser($this->member('member-2'));

        $old = [
            '/members/member-4/comments/' => '/members/member-4/comments',
            '/members/member-4/comments/add' => '/members/member-4/comment/add',
            '/members/member-4/comments/edit' => '/members/member-4/comment/edit',
            '/members/member-4/relations' => '/members/member-4/friends',
            '/members/member-4/relations/add' => '/members/member-4/friendship/add',
            '/members/member-4/relations/add/finish' => '/members/member-4/friendship/add',
            '/members/member-4/relations/delete/12/editprofile' => '/members/member-4/friendship/edit',
            '/myvisitors' => '/members/member-2/visitors',
        ];
        foreach ($old as $url => $new) {
            $client->request('GET', $url);
            self::assertResponseRedirects($new, 301, $url);
        }
    }

    public function testTheRemovedPagesAreGone(): void
    {
        $client = static::createClient();
        $client->loginUser($this->member('member-2'));

        foreach (['/retired', '/members/member-4/adminedit/finish'] as $url) {
            $client->request('GET', $url);
            self::assertResponseStatusCodeSame(404, $url);
        }
    }

    private function member(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}

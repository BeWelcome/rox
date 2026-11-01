<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The tabs under the profile's head: on the own profile they include My notes (with the number of notes) and, for
 * members who show profile visits, the profile views after it; nobody else sees them. The notes are a tab of the
 * profile, under its head; the member menu doesn't have them any more.
 */
#[Group('integration')]
final class ProfileTabsTest extends WebTestCase
{
    public function testMyNotesIsATabOfTheOwnProfileOnly(): void
    {
        $client = static::createClient();
        $member = $this->getMember('member-2');

        $client->loginUser($member);
        $crawler = $client->request('GET', '/members/member-2');
        self::assertResponseIsSuccessful();
        $tab = $crawler->filter('.p-hero__tabs a[href$="/members/member-2/notes"]');
        self::assertCount(1, $tab);
        self::assertCount(1, $tab->filter('i.fa-sticky-note'));
        // only the tab links to them
        self::assertCount(1, $crawler->filter('a[href$="/members/member-2/notes"]'));

        // the notes page is a tab of the profile
        $crawler = $client->request('GET', '/members/member-2/notes');
        self::assertResponseIsSuccessful();
        self::assertStringEndsWith('/members/member-2/notes', (string) $crawler->filter('.p-hero__tab--active')->attr('href'));

        $client->loginUser($this->getMember('member-4'));
        $crawler = $client->request('GET', '/members/member-2');
        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('.p-hero__tabs a[href*="/notes"]'));
    }

    public function testTheProfileViewsComeAfterMyNotesWithAnEye(): void
    {
        $client = static::createClient();
        $member = $this->getMember('member-2');
        $connection = static::getContainer()->get(EntityManagerInterface::class)->getConnection();
        $preferenceId = $connection->fetchOne("SELECT id FROM preferences WHERE codeName = 'PreferenceShowProfileVisits'");
        $connection->executeStatement('DELETE FROM memberspreferences WHERE IdMember = ? AND IdPreference = ?', [$member->getId(), $preferenceId]);
        $connection->executeStatement(
            "INSERT INTO memberspreferences (IdMember, IdPreference, Value, created, updated) VALUES (?, ?, 'Yes', NOW(), NOW())",
            [$member->getId(), $preferenceId],
        );
        static::getContainer()->get(EntityManagerInterface::class)->clear();

        $client->loginUser($this->getMember('member-2'));
        $crawler = $client->request('GET', '/members/member-2');

        self::assertResponseIsSuccessful();
        $links = $crawler->filter('.p-hero__tabs a')->each(static fn ($tab): string => (string) $tab->attr('href'));
        $notes = array_search(true, array_map(static fn (string $href): bool => str_ends_with($href, '/members/member-2/notes'), $links), true);
        $views = array_search(true, array_map(static fn (string $href): bool => str_contains($href, '/members/member-2/visitors'), $links), true);
        self::assertIsInt($notes);
        self::assertSame($notes + 1, $views, 'the profile views right after My notes');
        self::assertCount(1, $crawler->filter('.p-hero__tabs a[href*="/visitors"] i.fa-eye'));
    }

    /**
     * An empty tab shows the same box on every tab (profile/_tab_empty.html.twig), with the tab's own text and action.
     */
    public function testEmptyTabsShowTheSameBox(): void
    {
        $client = static::createClient();
        $member = $this->getMember('member-2');
        $connection = static::getContainer()->get(EntityManagerInterface::class)->getConnection();
        $connection->executeStatement('DELETE FROM mycontacts WHERE IdMember = ?', [$member->getId()]);
        $connection->executeStatement('DELETE FROM friend WHERE left_id = ? OR right_id = ?', [$member->getId(), $member->getId()]);
        $connection->executeStatement('DELETE gig FROM gallery_items_to_gallery gig JOIN gallery_items i ON i.id = gig.item_id_foreign WHERE i.user_id_foreign = ?', [$member->getId()]);
        $connection->executeStatement('DELETE FROM gallery_items WHERE user_id_foreign = ?', [$member->getId()]);

        $client->loginUser($member);
        foreach (['notes' => 0, 'friends' => 0, 'gallery' => 1] as $tab => $actions) {
            $crawler = $client->request('GET', '/members/member-2/' . $tab);
            self::assertResponseIsSuccessful();
            self::assertCount(1, $crawler->filter('.p-profile-main .p-tab-empty'), $tab);
            self::assertCount($actions, $crawler->filter('.p-tab-empty .p-tab-empty__action'), $tab);
        }
    }

    private function getMember(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}

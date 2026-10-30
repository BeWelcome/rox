<?php

namespace App\Tests\Controller;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * New members who haven't finished the signup see on every page until when they have to, before
 * members:remove:notconfirmed removes the account: the setup, or for those who did it the email confirmation.
 */
#[Group('integration')]
final class SetupBannerTest extends WebTestCase
{
    public function testTheBannerAsksForWhatIsMissing(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
        $client->loginUser($member);
        $setStatus = static function (string $status) use ($entityManager): void {
            $entityManager->getConnection()->executeStatement(
                "UPDATE member SET Status = ?, Reminders = 0 WHERE Username = 'member-2'",
                [$status],
            );
            $entityManager->clear();
        };

        $setStatus(MemberStatusType::MAIL_CONFIRMED);
        $crawler = $client->request('GET', '/forums');
        self::assertSelectorTextContains('.setup-banner__message', 'profile.finish.setup');
        self::assertStringEndsWith('/members/member-2/finalize', $this->action($crawler));

        // set up, but the email address isn't confirmed
        $setStatus(MemberStatusType::ACCOUNT_ACTIVATED);
        $crawler = $client->request('GET', '/forums');
        self::assertSelectorTextContains('.setup-banner__message', 'profile.confirm.email.deadline');
        self::assertStringEndsWith('/members/member-2/confirm', $this->action($crawler));

        $setStatus(MemberStatusType::ACTIVE);
        $crawler = $client->request('GET', '/forums');
        self::assertCount(0, $crawler->filter('.setup-banner'));
    }

    public function testTheBannerNamesADeadlineBeforeTheRemoval(): void
    {
        self::assertLessThan(Member::DAYS_TO_FINISH_SIGNUP, Member::DAYS_SHOWN_TO_FINISH_SIGNUP);
    }

    private function action(Crawler $crawler): string
    {
        return (string) $crawler->filter('.setup-banner__action')->attr('href');
    }
}

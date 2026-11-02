<?php

namespace App\Tests\Controller\Admin;

use App\Doctrine\CommentAdminActionType;
use App\Doctrine\CommentQualityType;
use App\Entity\Comment;
use App\Entity\CommentReport;
use App\Entity\Member;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The comments tool, as the volunteers use it: a list with the chosen comment next to it, and its actions, which lead
 * back to the list (with the next comment when the chosen one left it). bwadmin has the Comments right and is in the
 * Safety Team (fixtures/rightsvolunteers.yml); member-9 gets only the Comments right here.
 */
#[Group('integration')]
final class CommentsToolTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testTheListShowsTheReportedCommentWithTheMembersReport(): void
    {
        [$older, $newer] = $this->commentsToCheck();
        $this->entityManager->persist(new CommentReport($newer, $newer->getToMember(), 'zztest we never met'));
        $this->entityManager->flush();
        $this->client->loginUser($this->member('bwadmin'));

        $crawler = $this->client->request('GET', '/admin/comment/reported');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.p-hero__tab--active', 'Negative/reported');
        self::assertSelectorExists('.p-hero__tab--active .p-hero__tab-count');
        // the newest first, and chosen
        self::assertSame('zztest newer', trim($crawler->filter('.c-cadmin-row.is-selected .c-cadmin-row__excerpt')->text()));
        self::assertCount(1, $crawler->filter('.c-cadmin-row.is-selected .c-tag:contains("Reported")'));
        self::assertSelectorTextContains('.c-cadmin-report', 'zztest we never met');
        self::assertSelectorTextContains('.c-cadmin-detail__text', 'zztest newer');

        $crawler = $this->client->request('GET', '/admin/comment/reported?selected=' . $older->getId());
        self::assertSelectorTextContains('.c-cadmin-detail__text', 'zztest older');
        self::assertSelectorNotExists('.c-cadmin-report');
        // negative and nobody reported it
        self::assertSelectorTextContains('.c-cadmin-detail__info', 'Not reported');
        self::assertCount(1, $crawler->filter('form[action$="/safetyteam"]'));
    }

    public function testAVolunteerWithoutTheSafetyTeamDoesntSendCommentsToThem(): void
    {
        [, $newer] = $this->commentsToCheck();
        $volunteer = $this->commentsVolunteer();
        $this->client->loginUser($volunteer);

        $crawler = $this->client->request('GET', '/admin/comment/reported');

        self::assertResponseIsSuccessful();
        self::assertSame(['Negative/reported', 'Checked', 'All written'], $crawler->filter('.p-hero__tab')->each(
            static fn (Crawler $tab) => trim(preg_replace('/\s*\d+$/', '', trim($tab->text())))
        ));
        self::assertCount(0, $crawler->filter('form[action$="/safetyteam"]'));

        $this->client->request('GET', '/admin/comment/safetyteam');
        self::assertResponseStatusCodeSame(403);

        $this->client->request('POST', $this->url($newer, 'safetyteam'), [
            '_token' => $this->token($crawler),
        ]);
        self::assertResponseStatusCodeSame(403);
        self::assertSame(CommentAdminActionType::ADMIN_CHECK, $this->reload($newer)->getAdminAction());
    }

    public function testMarkingAsCheckedShowsTheNextCommentOfTheList(): void
    {
        [$older, $newer] = $this->commentsToCheck();
        $this->client->loginUser($this->member('bwadmin'));

        $crawler = $this->client->request('GET', '/admin/comment/reported');
        $this->client->submit($crawler->filter('form[action$="/checked"]')->form());

        self::assertResponseRedirects('/admin/comment/reported?selected=' . $older->getId());
        self::assertSame(CommentAdminActionType::ADMIN_CHECKED, $this->reload($newer)->getAdminAction());

        // checked: in the checked list, and back to check from there
        $crawler = $this->client->request('GET', '/admin/comment/checked?selected=' . $newer->getId());
        self::assertSelectorTextContains('.c-cadmin-detail__tags', 'Checked');
        self::assertCount(0, $crawler->filter('form[action$="/checked"]'));
        $this->client->submit($crawler->filter('form[action$="/recheck"]')->form());
        self::assertSame(CommentAdminActionType::ADMIN_CHECK, $this->reload($newer)->getAdminAction());
    }

    public function testTheSwitchesHideAndLockTheCommentAndStayOnIt(): void
    {
        [, $newer] = $this->commentsToCheck();
        $newer->setEditingAllowed(true);
        $this->entityManager->flush();
        $this->client->loginUser($this->member('bwadmin'));
        $page = '/admin/comment/reported?selected=' . $newer->getId();

        $crawler = $this->client->request('GET', $page);
        self::assertSame('true', $crawler->filter('form[action$="/hide"] [role="switch"]')->attr('aria-checked'));
        $this->client->submit($crawler->filter('form[action$="/hide"]')->form());
        self::assertResponseRedirects($page);
        self::assertFalse($this->reload($newer)->getShowToOtherMembers());

        $crawler = $this->client->request('GET', $page);
        self::assertSame('false', $crawler->filter('form[action$="/show"] [role="switch"]')->attr('aria-checked'));
        $this->client->submit($crawler->filter('form[action$="/lock"]')->form());
        self::assertFalse($this->reload($newer)->getEditingAllowed());

        $crawler = $this->client->request('GET', $page);
        $this->client->submit($crawler->filter('form[action$="/unlock"]')->form());
        self::assertTrue($this->reload($newer)->getEditingAllowed());
        $this->client->submit($this->client->request('GET', $page)->filter('form[action$="/show"]')->form());
        self::assertTrue($this->reload($newer)->getShowToOtherMembers());
    }

    public function testDeletingRemovesTheCommentAndItsReports(): void
    {
        [$older, $newer] = $this->commentsToCheck();
        $this->entityManager->persist(new CommentReport($newer, $newer->getToMember(), 'zztest report'));
        $this->entityManager->flush();
        $id = $newer->getId();
        $this->client->loginUser($this->member('bwadmin'));

        $crawler = $this->client->request('GET', '/admin/comment/reported?selected=' . $id);
        self::assertSelectorTextContains('#c-cadmin-delete', 'Delete this comment?');
        $this->client->submit($crawler->filter('#c-cadmin-delete form[action$="/delete"]')->form());

        self::assertResponseRedirects('/admin/comment/reported?selected=' . $older->getId());
        $this->entityManager->clear();
        self::assertNull($this->entityManager->find(Comment::class, $id));
        self::assertSame([], $this->entityManager->getRepository(CommentReport::class)->findBy(['comment' => $id]));
    }

    public function testAnActionOnlyReturnsToTheCommentsTool(): void
    {
        [, $newer] = $this->commentsToCheck();
        $this->client->loginUser($this->member('bwadmin'));
        $crawler = $this->client->request('GET', '/admin/comment/reported');

        $this->client->request('POST', $this->url($newer, 'hide'), [
            '_token' => $this->token($crawler),
            'return' => 'https://example.com/admin/comment',
        ]);

        self::assertResponseRedirects('/admin/comment/for/' . $newer->getToMember()->getUsername() . '?selected=' . $newer->getId());
    }

    public function testTheLinkOfAMailShowsTheCommentAmongTheCommentsAboutTheMember(): void
    {
        [, $newer] = $this->commentsToCheck();
        $this->client->loginUser($this->member('bwadmin'));

        $this->client->request('GET', '/admin/comment/' . $newer->getToMember()->getUsername() . '/' . $newer->getFromMember()->getUsername());
        self::assertResponseRedirects('/admin/comment/for/' . $newer->getToMember()->getUsername() . '?selected=' . $newer->getId());

        $this->client->followRedirect();
        self::assertSelectorTextContains('.c-cadmin__filter', 'Comments about ' . $newer->getToMember()->getUsername());
        self::assertSelectorTextContains('.c-cadmin-detail__text', 'zztest newer');
    }

    public function testAReportedCommentKeepsTheReport(): void
    {
        [, $newer] = $this->commentsToCheck();
        $newer->setAdminAction(CommentAdminActionType::NOTHING_NEEDED);
        $this->entityManager->flush();
        $receiver = $newer->getToMember();
        $this->client->loginUser($receiver);

        $crawler = $this->client->request('GET', '/members/' . $receiver->getUsername() . '/comment/' . $newer->getFromMember()->getUsername() . '/report');
        $this->client->submit($crawler->filter('#report_comment_SendFeedback')->form(), [
            'report_comment[feedback]' => '<p>zztest he never stayed with me</p>',
        ]);

        self::assertResponseRedirects();
        $reports = $this->entityManager->getRepository(CommentReport::class)->findBy(['comment' => $newer->getId()]);
        self::assertCount(1, $reports);
        self::assertSame('zztest he never stayed with me', $reports[0]->getText());
        self::assertSame($receiver->getId(), $reports[0]->getReporter()->getId());
        self::assertSame(CommentAdminActionType::ADMIN_CHECK, $this->reload($newer)->getAdminAction());
    }

    /**
     * Two negative comments to check, written a minute apart (about member-2 and member-3).
     *
     * @return array{Comment, Comment} the older and the newer one
     */
    private function commentsToCheck(): array
    {
        $comments = [];
        foreach ([['member-2', 'member-6', 'zztest older', '+1 year'], ['member-3', 'member-7', 'zztest newer', '+1 year +1 minute']] as [$to, $from, $text, $when]) {
            $comment = $this->entityManager->getRepository(Comment::class)->findOneBy([
                'toMember' => $this->member($to), 'fromMember' => $this->member($from),
            ]) ?? new Comment()->setToMember($this->member($to))->setFromMember($this->member($from));
            $comment
                ->setQuality(CommentQualityType::NEGATIVE)
                ->setComment($text)
                ->setAdminAction(CommentAdminActionType::ADMIN_CHECK)
                ->setEditingAllowed(false);
            $this->entityManager->persist($comment);
            $comments[$when] = $comment;
        }
        $this->entityManager->flush();
        // after the first save, which sets created to now
        foreach ($comments as $when => $comment) {
            $comment->setCreated(new DateTime($when));
        }
        $this->entityManager->flush();
        $comments = array_values($comments);

        return $comments;
    }

    private function commentsVolunteer(): Member
    {
        $member = $this->member('member-9');
        $this->entityManager->getConnection()->executeStatement(
            "INSERT INTO rightsvolunteers (IdMember, IdRight, Level, Scope, Comment, created, updated)
             SELECT ?, id, 1, '\"All\"', '', NOW(), NOW() FROM rights WHERE Name = 'Comments'",
            [$member->getId()],
        );

        return $member;
    }

    private function token(Crawler $crawler): string
    {
        return $crawler->filter('form.c-cadmin-form input[name="_token"]')->attr('value');
    }

    private function url(Comment $comment, string $action): string
    {
        return '/admin/comment/' . $comment->getToMember()->getUsername() . '/' . $comment->getFromMember()->getUsername() . '/' . $action;
    }

    private function reload(Comment $comment): Comment
    {
        $this->entityManager->clear();

        return $this->entityManager->find(Comment::class, $comment->getId());
    }

    private function member(string $username): Member
    {
        return $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
    }
}

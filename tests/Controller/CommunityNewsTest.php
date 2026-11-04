<?php

namespace App\Tests\Controller;

use App\Entity\CommunityNews;
use App\Entity\CommunityNewsComment;
use App\Entity\CommunityNewsCommentReport;
use App\Entity\Member;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Community news as members, the news team and the checkers use it: comments and threaded replies, reports of
 * comments, the checkers' Keep and Delete, the news admin's list, editor and Hide/Publish. bwadmin has the
 * CommunityNews and Checker rights (fixtures/rightsvolunteers.yml).
 */
#[Group('integration')]
final class CommunityNewsTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testMembersCommentAndReplyInThreads(): void
    {
        $news = $this->news();
        $url = '/communitynews/' . $news->getId();

        $this->client->loginUser($this->member('member-3'));
        $crawler = $this->client->request('GET', $url);
        self::assertResponseIsSuccessful();
        $this->client->submit($crawler->filter('form[data-comment-form]')->form(['community_news_comment[text]' => "zztest first\nsecond line"]));
        $first = $this->lastComment($news);
        self::assertResponseRedirects($url . '#comment-' . $first->getId());
        // main shows a title: the start of the text
        self::assertSame('zztest first second line', $first->getTitle());

        // replies, three deep: the third stays at the second indentation and names whom it answers
        $parent = $first;
        foreach (['member-4', 'member-5', 'member-6'] as $i => $username) {
            $this->client->loginUser($this->member($username));
            $this->post($url, 'zztest reply ' . ($i + 1), $parent->getId());
            $parent = $this->lastComment($news);
        }

        $crawler = $this->client->request('GET', $url);
        $first = $crawler->filter('#comment-' . $first->getId());
        self::assertStringContainsString('is-top', (string) $first->attr('class'));
        // plain text with its line breaks
        self::assertMatchesRegularExpression('~zztest first<br ?/?>\s*second line~', $first->filter('.c-cnews-comment__text')->html());
        $replies = $crawler->filter('.c-cnews-comment.is-reply')->reduce(static fn (Crawler $row): bool => str_contains($row->text(), 'zztest reply'));
        self::assertCount(3, $replies);
        self::assertStringContainsString('indent-1', (string) $replies->eq(0)->attr('class'));
        self::assertStringContainsString('indent-2', (string) $replies->eq(1)->attr('class'));
        self::assertStringContainsString('indent-2', (string) $replies->eq(2)->attr('class'));
        self::assertCount(0, $replies->eq(1)->filter('.c-cnews-comment__to'));
        self::assertStringContainsString('member-5', $replies->eq(2)->filter('.c-cnews-comment__to')->text());
    }

    public function testAReplyMustAnswerACommentOfTheSameNews(): void
    {
        $news = $this->news();
        $other = $this->otherNews();
        $foreign = $this->comment($other, 'member-4', 'zztest elsewhere');
        $this->client->loginUser($this->member('member-3'));

        $this->post('/communitynews/' . $news->getId(), 'zztest wrong place', $foreign->getId());

        self::assertResponseStatusCodeSame(422);
        self::assertNull($this->entityManager->getRepository(CommunityNewsComment::class)->findOneBy(['text' => 'zztest wrong place']));
    }

    public function testAMemberReportsAnotherMembersCommentOnce(): void
    {
        $news = $this->news();
        $comment = $this->comment($news, 'member-4', 'zztest rude');
        $own = $this->comment($news, 'member-3', 'zztest mine');
        $url = '/communitynews/' . $news->getId();
        $this->client->loginUser($this->member('member-3'));

        $crawler = $this->client->request('GET', $url);
        // no Report on your own comment
        self::assertCount(0, $crawler->filter('#comment-' . $own->getId() . ' [data-report]'));
        $report = $crawler->filter('[data-report-form]')->form();
        $action = '/communitynews/comment/' . $comment->getId() . '/report';
        self::assertSame($action, $crawler->filter('#comment-' . $comment->getId() . ' [data-report]')->attr('data-report'));
        $token = $report->get('community_news_comment_report[_token]')->getValue();

        // "Something else" needs a note
        $this->client->request('POST', $action, ['community_news_comment_report' => ['reason' => 'other', 'note' => '', '_token' => $token]]);
        self::assertResponseRedirects($url . '#comment-' . $comment->getId());
        self::assertSame(0, $this->reports($comment));

        $this->client->request('POST', $action, ['community_news_comment_report' => ['reason' => 'other', 'note' => 'zztest off-topic', '_token' => $token]]);
        self::assertSame(1, $this->reports($comment));
        $this->client->request('POST', $action, ['community_news_comment_report' => ['reason' => 'spam', 'note' => '', '_token' => $token]]);
        self::assertSame(1, $this->reports($comment), 'a member reports a comment once');

        $crawler = $this->client->request('GET', $url);
        self::assertCount(1, $crawler->filter('#comment-' . $comment->getId() . ' .c-cnews-action--reported'));

        $this->client->request('POST', '/communitynews/comment/' . $own->getId() . '/report', ['community_news_comment_report' => ['reason' => 'spam', '_token' => $token]]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testTheCheckersKeepOrDeleteReportedComments(): void
    {
        $news = $this->news();
        $kept = $this->comment($news, 'member-4', 'zztest kept');
        $alone = $this->comment($news, 'member-5', 'zztest alone');
        $parent = $this->comment($news, 'member-6', 'zztest with replies');
        $reply = $this->comment($news, 'member-7', 'zztest a reply', $parent);
        $this->report($kept, 'member-2', 'spam');
        $this->report($kept, 'member-3', 'spam');
        $this->report($kept, 'member-8', 'other', 'zztest note');
        $this->report($alone, 'member-2', 'offensive');
        $this->report($parent, 'member-2', 'harassment');
        $this->client->loginUser($this->member('bwadmin'));
        // the request reads the reports, not the comments as cached here before they had any
        $this->entityManager->clear();

        $crawler = $this->client->request('GET', '/admin/spam/communitynews');
        self::assertResponseIsSuccessful();
        self::assertSame('3', trim($crawler->filter('.c-cnews-menu__item.is-active .c-cnews-menu__count')->text()));
        $row = $crawler->filter('.c-cnews-reported__row')->first();
        // the most reported first, with its reasons and notes
        self::assertStringContainsString('zztest kept', $row->text());
        self::assertStringContainsString('(2)', $row->filter('.c-cnews-reported__reasons')->text());
        self::assertStringContainsString('zztest note', $row->filter('.c-cnews-reported__note')->text());
        $form = $crawler->filter('form[data-select-form]');
        $token = $form->filter('input[name="_token"]')->attr('value');

        $this->client->request('POST', '/admin/spam/communitynews', ['_token' => $token, 'keep' => $kept->getId()]);
        self::assertResponseRedirects('/admin/spam/communitynews');
        self::assertSame(0, $this->reports($kept));
        self::assertNotNull($this->reload($kept));

        // deleting the selected ones: one without replies goes, one with replies stays as "Comment removed"
        $this->client->request('POST', '/admin/spam/communitynews', ['_token' => $token, 'ids' => [$alone->getId(), $parent->getId()]]);
        self::assertNull($this->reload($alone));
        $removed = $this->reload($parent);
        self::assertTrue($removed->isDeleted());
        self::assertSame('', $removed->getText());
        self::assertNotNull($this->reload($reply));

        $crawler = $this->client->request('GET', '/communitynews/' . $news->getId());
        self::assertCount(1, $crawler->filter('#comment-' . $parent->getId() . ' .c-cnews-comment__removed'));
        self::assertCount(1, $crawler->filter('#comment-' . $reply->getId()));

        $crawler = $this->client->request('GET', '/admin/spam/communitynews');
        self::assertSelectorExists('.c-cnews-empty');
        $this->client->request('POST', '/admin/spam/communitynews', ['_token' => 'wrong', 'delete' => $kept->getId()]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testTheNewsTeamWritesHidesAndPublishesNews(): void
    {
        $this->client->loginUser($this->member('bwadmin'));

        $crawler = $this->client->request('GET', '/admin/communitynews/create');
        self::assertResponseIsSuccessful();
        self::assertSame('news', $crawler->filter('textarea[data-editor-toolbar]')->attr('data-editor-toolbar'));
        $this->client->submit($crawler->filter('form[data-news-editor]')->form([
            'community_news[title]' => 'zztest hidden news',
            'community_news[text]' => '<h2>zztest heading</h2><p>zztest text</p>',
        ]));
        self::assertResponseRedirects('/admin/communitynews');
        $news = $this->entityManager->getRepository(CommunityNews::class)->findOneBy(['title' => 'zztest hidden news']);
        self::assertFalse($news->getPublic());

        $crawler = $this->client->request('GET', '/admin/communitynews', ['show' => 'hidden']);
        self::assertSame('/admin/communitynews?show=hidden', $crawler->filter('.c-cnews-segments__item.is-active')->attr('href'));
        $card = $crawler->filter('.c-cnews-post')->reduce(static fn (Crawler $card): bool => str_contains($card->text(), 'zztest hidden news'));
        self::assertCount(1, $card);
        self::assertStringContainsString('is-hidden', (string) $card->attr('class'));

        // members don't see a hidden news; the news team does
        $this->client->loginUser($this->member('member-3'));
        $this->client->request('GET', '/communitynews/' . $news->getId());
        self::assertResponseStatusCodeSame(404);

        $this->client->loginUser($this->member('bwadmin'));
        $this->client->request('GET', '/admin/communitynews/' . $news->getId() . '/show');
        self::assertResponseStatusCodeSame(405);
        $this->client->submit($card->filter('form')->form());
        self::assertResponseRedirects('/admin/communitynews?show=hidden');
        self::assertTrue($this->reload($news)->getPublic());

        $crawler = $this->client->request('GET', '/communitynews/' . $news->getId());
        // the article's HTML is purified, headings stay
        self::assertSelectorTextContains('.c-cnews-article__body h2', 'zztest heading');

        $crawler = $this->client->request('GET', '/admin/communitynews/' . $news->getId() . '/edit');
        self::assertSelectorExists('.c-cnews-history');
        $this->client->submit($crawler->filter('form[data-news-editor]')->form(['community_news[title]' => 'zztest renamed news']));
        self::assertSame('zztest renamed news', $this->reload($news)->getTitle());
        self::assertSame('bwadmin', $this->reload($news)->getUpdatedBy()->getUsername());
    }

    private function post(string $url, string $text, ?int $parent = null): void
    {
        $crawler = $this->client->request('GET', $url);
        $form = $crawler->filter('form[data-comment-form]')->form(['community_news_comment[text]' => $text]);
        $values = $form->getPhpValues();
        $values['community_news_comment']['parent'] = null === $parent ? '' : (string) $parent;
        $this->client->request('POST', $url, $values);
    }

    private function news(): CommunityNews
    {
        return $this->entityManager->getRepository(CommunityNews::class)->findOneBy(['title' => 'The very first community news']);
    }

    private function otherNews(): CommunityNews
    {
        $other = new CommunityNews();
        $other->setTitle('zztest other news')->setText('<p>zztest</p>')->setPublic(true)->setCreatedBy($this->member('member-2'));
        $other->setCreatedAt(new DateTime());
        $this->entityManager->persist($other);
        $this->entityManager->flush();

        return $other;
    }

    private function comment(CommunityNews $news, string $author, string $text, ?CommunityNewsComment $parent = null): CommunityNewsComment
    {
        $news = $this->entityManager->find(CommunityNews::class, $news->getId());
        $parent = null === $parent ? null : $this->entityManager->find(CommunityNewsComment::class, $parent->getId());
        $comment = new CommunityNewsComment();
        $comment->setCommunityNews($news)->setAuthor($this->member($author))->setText($text)->setTitle($text)->setParent($parent);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return $comment;
    }

    private function report(CommunityNewsComment $comment, string $reporter, string $reason, ?string $note = null): void
    {
        $comment = $this->entityManager->find(CommunityNewsComment::class, $comment->getId());
        $this->entityManager->persist(new CommunityNewsCommentReport($comment, $this->member($reporter), $reason, $note));
        $this->entityManager->flush();
    }

    private function reports(CommunityNewsComment $comment): int
    {
        return (int) $this->entityManager->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM community_news_comment_report WHERE comment_id = ?',
            [$comment->getId()],
        );
    }

    private function lastComment(CommunityNews $news): CommunityNewsComment
    {
        $this->entityManager->clear();

        return $this->entityManager->getRepository(CommunityNewsComment::class)->findOneBy(['communityNews' => $news->getId()], ['id' => 'DESC']);
    }

    /**
     * @template T of object
     *
     * @param T $entity
     *
     * @return T|null
     */
    private function reload(object $entity): ?object
    {
        $this->entityManager->clear();

        return $this->entityManager->find($entity::class, $entity->getId());
    }

    private function member(string $username): Member
    {
        return $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
    }
}

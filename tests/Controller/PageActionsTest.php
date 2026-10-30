<?php

namespace App\Tests\Controller;

use App\Entity\Comment;
use App\Entity\LoginMessage;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\ProfileNote;
use App\Entity\Trip;
use App\Model\WikiModel;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Actions that change something are POST requests with a token, the pages post them; the wiki's HTML is cleaned;
 * the old search URLs lead to the search.
 */
#[Group('integration')]
final class PageActionsTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function actions(): array
    {
        return [
            'delete a conversation' => ['member-2', '/conversation/1/delete'],
            'purge a conversation' => ['member-2', '/conversation/1/purge'],
            'recover a conversation' => ['member-2', '/conversation/1/recover'],
            'decline a request' => ['member-2', '/conversation/1/decline'],
            'not spam' => ['member-2', '/conversation/1/nospam'],
            'remove a trip' => ['member-2', '/trip/1/remove'],
            'delete a note' => ['member-2', '/members/member-3/note/delete'],
            'remove a friendship' => ['member-2', '/members/member-3/friendship/remove'],
            'delete a profile language' => ['member-2', '/members/member-2/delete/language/de'],
            'hide a comment' => ['bwadmin', '/admin/comment/member-1/member-2/hide'],
            'show a comment' => ['bwadmin', '/admin/comment/member-1/member-2/show'],
            'comment to the safety team' => ['bwadmin', '/admin/comment/member-1/member-2/safetyteam'],
            'comment checked' => ['bwadmin', '/admin/comment/member-1/member-2/checked'],
        ];
    }

    #[DataProvider('actions')]
    public function testActionsNeedAPostWithToken(string $username, string $url): void
    {
        $this->client->loginUser($this->member($username));

        // not this action (405, or another route that answers a GET with an error)
        $this->client->request('GET', $url);
        self::assertGreaterThanOrEqual(400, $this->client->getResponse()->getStatusCode());

        $this->client->request('POST', $url);
        self::assertResponseStatusCodeSame(403);
    }

    public function testThePagesPostTheirActionsWithTheToken(): void
    {
        $member = $this->member('member-2');
        $this->client->loginUser($member);

        // the conversation's delete button (on its latest message)
        $this->submit('/conversation/1', '/conversation/3/delete');
        $deleteRequest = (string) $this->find(Message::class, 3)->getDeleteRequest();
        self::assertStringContainsString('receiverdeleted', $deleteRequest);

        // the trip's remove button (in the confirmation)
        $this->submit('/trips', '/trip/1/remove');
        self::assertNotNull($this->find(Trip::class, 1)->getDeleted());

        // the note's delete button
        $member = $this->member('member-2');
        $note = new ProfileNote()
            ->setOwner($member)
            ->setMember($this->member('member-3'))
            ->setCategory('')
            ->setComment('A note')
            ->setCreated(new DateTime())->setUpdated(new DateTime());
        $this->entityManager->persist($note);
        $this->entityManager->flush();
        $this->submit('/members/member-2/notes', '/members/member-3/note/delete');
        self::assertNull($this->entityManager->getRepository(ProfileNote::class)->findOneBy(['owner' => $member]));
    }

    public function testTheAdminPagesPostTheirActionsWithTheToken(): void
    {
        $admin = $this->member('bwadmin');
        // the login messages are for the admins
        $this->entityManager->getConnection()->executeStatement(
            "INSERT INTO rightsvolunteers (IdMember, IdRight, Level, Scope, Comment, created, updated)
             SELECT ?, id, 10, '\"All\"', '', NOW(), NOW() FROM rights WHERE Name = 'Admin'",
            [$admin->getId()],
        );
        $this->client->loginUser($admin);

        $this->submit('/admin/comment', '/admin/comment/member-1/member-2/hide');
        $comment = $this->entityManager->getRepository(Comment::class)->findOneBy([
            'toMember' => $this->member('member-1'),
            'fromMember' => $this->member('member-2'),
        ]);
        self::assertFalse($comment->getShowToOtherMembers());

        $message = new LoginMessage()
            ->setMessage('Hello')
            ->setCreated(new DateTime())
            ->setExpires(new DateTime('+1 week'));
        $this->entityManager->persist($message);
        $this->entityManager->flush();
        $expire = '/admin/tools/login_message/' . $message->getId() . '/expire';
        $this->client->request('POST', $expire);
        self::assertResponseStatusCodeSame(403);
        $this->submit('/admin/tools/login_messages/show', $expire);
        self::assertLessThanOrEqual(new DateTime(), $this->find(LoginMessage::class, $message->getId())->getExpires());
    }

    public function testTheProfileUpdatesNeedATokenOrJson(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('POST', '/members/update/field', [
            'language' => 'en', 'field' => 'Occupation', 'content' => 'x', 'username' => 'member-2',
        ]);
        self::assertResponseStatusCodeSame(403);

        // a form on another site can't send JSON
        $this->client->request('POST', '/members/update/maxguests', ['maxGuests' => 9]);
        self::assertResponseStatusCodeSame(415);

        // only the admins may set a member's status
        $this->client->request('POST', '/members/status/set', [
            'profile_status_form' => ['status' => 'Banned', 'member' => 3],
        ]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testTheFeedbackFormHasAToken(): void
    {
        $crawler = $this->client->request('GET', '/feedback');

        // the login forms' tokens are _csrf_token
        self::assertCount(1, $crawler->filter('form input[name="_token"]'));
    }

    public function testTheWikiDropsScripts(): void
    {
        $this->client->loginUser($this->member('member-2'));
        static::getContainer()->get(WikiModel::class)->createWikiPage(
            'SecurityTest',
            "{| class=\"wikitable\" onmouseover=\"alert(1)\"\n| cell\n|}\n\n<script>alert(2)</script>",
        );

        $crawler = $this->client->request('GET', '/wiki/SecurityTest');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('#wiki table.wikitable, .wiki table.wikitable, table.wikitable'));
        self::assertStringNotContainsString('onmouseover', (string) $this->client->getResponse()->getContent());
        $page = $crawler->filter('table.wikitable')->ancestors()->first()->html();
        self::assertStringNotContainsString('alert(2)', $page);
    }

    public function testTheLegacySearchUrlsGoToTheSearch(): void
    {
        $this->client->loginUser($this->member('member-2'));

        foreach (['/search', '/search/members/text', '/search/members/map/advanced'] as $url) {
            $this->client->request('GET', $url);
            self::assertResponseRedirects('/search/locations', 301, $url);
        }
    }

    /**
     * Submits the page's form that posts to the action.
     */
    private function submit(string $page, string $action): void
    {
        $this->entityManager->clear();
        $crawler = $this->client->request('GET', $page);
        self::assertResponseIsSuccessful();
        // the conversation's head has the action twice (icon and menu)
        $form = $crawler->filter('form[action$="' . $action . '"]');
        self::assertGreaterThan(0, $form->count(), 'no form for ' . $action . ' on ' . $page);
        $this->client->submit($form->first()->form());
        self::assertResponseRedirects();
        $this->entityManager->clear();
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private function find(string $class, int $id): object
    {
        $entity = $this->entityManager->find($class, $id);
        self::assertNotNull($entity);

        return $entity;
    }

    private function member(string $username): Member
    {
        $member = $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}

<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use App\Entity\ProfileNote;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('integration')]
/**
 * @infection-ignore-all
 */
class NoteControllerTest extends WebTestCase
{
    public function testNotesPageRendersDeleteModalTargetAndTextOnlyPreviewForImageNotes(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $owner = $this->reloadMember('member-2');
        $contact = $this->reloadMember('member-5');
        $note = $this->createImageNote($entityManager, $owner, $contact);

        try {
            $this->loginMember($client, $owner);

            $crawler = $client->request('GET', '/members/' . $owner->getUsername() . '/notes');

            $this->assertResponseIsSuccessful();
            $trigger = $crawler->filter('[data-micromodal-trigger="note-delete-' . $note->getId() . '"]');
            self::assertCount(1, $trigger);

            $modal = $crawler->filter('#note-delete-' . $note->getId());
            self::assertCount(1, $modal);
            self::assertSame('true', $modal->attr('aria-hidden'));
            self::assertStringContainsString('This is a test note.', $modal->text());
            self::assertCount(0, $modal->filter('img'));
            self::assertCount(0, $modal->filter('figure'));
        } finally {
            $entityManager->remove($note);
            $entityManager->flush();
        }
    }

    public function testANoteGetsSeveralCategoriesAndTheListFiltersByEach(): void
    {
        $client = static::createClient();
        $this->loginMember($client, $this->reloadMember('member-2'));

        $this->saveNote($client, 'zztest hosts,zztest friends', 'Met at the lake.');
        $this->assertResponseRedirects();
        self::assertSame(['zztest friends', 'zztest hosts'], $this->note()->getCategoryNames());

        $crawler = $client->request('GET', '/members/member-2/notes');
        $card = $crawler->filter('.p-notes-card')->reduce(static fn ($card): bool => str_contains($card->text(), 'Met at the lake.'));
        self::assertSame(['zztest friends', 'zztest hosts'], $card->filter('.p-notes-card__category')->each(static fn ($pill): string => $pill->text()));
        foreach (['zztest hosts', 'zztest friends'] as $category) {
            $crawler = $client->request('GET', '/members/member-2/notes?category=' . rawurlencode($category));
            self::assertStringContainsString('Met at the lake.', $crawler->filter('main')->text(), 'the filter ' . $category . ' finds the note');
        }

        // editing: one category stays, one goes, one comes
        $this->saveNote($client, 'zztest friends,zztest cyclists', 'Met at the lake.');
        self::assertSame(['zztest cyclists', 'zztest friends'], $this->note()->getCategoryNames());
    }

    public function testANoteNeedsACategory(): void
    {
        $client = static::createClient();
        $this->loginMember($client, $this->reloadMember('member-2'));

        $this->saveNote($client, '', 'No category.');

        $this->assertResponseStatusCodeSame(200);
        self::assertNull($this->note());
        self::assertStringContainsString('profile.note.categories.needed', $client->getResponse()->getContent());
    }

    public function testOutsideTheSidePanelTheNoteFormIsAPageOfItsOwn(): void
    {
        $client = static::createClient();
        $this->loginMember($client, $this->reloadMember('member-2'));

        $crawler = $client->request('GET', '/members/member-3/note/add');

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('form.p-note-form'), 'the note form of the side panel, as a page');
        self::assertCount(1, $crawler->filter('input[name="profile_note[categories]"]'));
    }

    /**
     * member-2 saves their note about member-3 on the note page (outside the side panel).
     */
    private function saveNote(KernelBrowser $client, string $categories, string $text): void
    {
        $crawler = $client->request('GET', '/members/member-3/note/add');
        if ($client->getResponse()->isRedirection()) {
            // a note about the member exists: its edit page
            $crawler = $client->followRedirect();
        }
        $form = $crawler->filter('form.p-note-form')->form();
        $form['profile_note[categories]'] = $categories;
        $form['profile_note[comment]'] = '<p>' . $text . '</p>';
        $client->submit($form);
    }

    private function note(): ?ProfileNote
    {
        $entityManager = $this->getEntityManager();
        $entityManager->clear();

        return $entityManager->getRepository(ProfileNote::class)->findOneBy([
            'owner' => $this->reloadMember('member-2'),
            'member' => $this->reloadMember('member-3'),
        ]);
    }

    private function createImageNote(EntityManagerInterface $entityManager, Member $owner, Member $contact): ProfileNote
    {
        $note = new ProfileNote();
        $note->setOwner($owner);
        $note->setMember($contact);
        $note->setCategoryNames(['Issue 344 regression']);
        $note->setComment(
            '<figure class="image"><img src="/images/homepicture-1200px_2-min.jpg" alt="wide test image"></figure>'
            . '<p>This is a test note.</p>'
            . '<figure class="image"><img src="/images/homepicture-1200px_3-min.jpg" alt="wide test image"></figure>'
        );
        $entityManager->persist($note);
        $entityManager->flush();

        return $note;
    }

    private function loginMember(KernelBrowser $client, Member $member): void
    {
        $client->loginUser($member);
    }

    private function reloadMember(string $username): Member
    {
        $entityManager = $this->getEntityManager();
        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
        $this->assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}

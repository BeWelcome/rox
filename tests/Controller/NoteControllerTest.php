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

    private function createImageNote(EntityManagerInterface $entityManager, Member $owner, Member $contact): ProfileNote
    {
        $note = new ProfileNote();
        $note->setOwner($owner);
        $note->setMember($contact);
        $note->setCategory('Issue 344 regression');
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

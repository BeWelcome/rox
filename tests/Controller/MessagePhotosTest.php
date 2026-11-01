<?php

namespace App\Tests\Controller;

use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Photos in messages: the reply box has a camera that uploads through the editor's image upload,
 * a photo in a message shows in its bubble, and a message with only photos says so in the list.
 */
#[Group('integration')]
class MessagePhotosTest extends WebTestCase
{
    public function testAPhotoInAMessageShowsInTheConversationAndTheReplyBoxCanAddPhotos(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $members = $entityManager->getRepository(Member::class);
        $receiver = $members->findOneBy(['username' => 'member-2']);
        $sender = $members->findOneBy(['username' => 'member-5']);

        $subject = new Subject()->setSubject('zztest photo subject');
        $entityManager->persist($subject);
        $message = new Message()
            ->setSender($sender)
            ->setReceiver($receiver)
            ->setInitiator($sender)
            ->setSubject($subject)
            ->setMessage('<p><img src="/gallery/show/uploaded/1/zztestphoto" alt=""></p>')
            ->setStatus(MessageStatusType::SENT)
            ->setFolder(InFolderType::NORMAL);
        $entityManager->persist($message);
        $entityManager->flush();

        $client->loginUser($receiver);
        $crawler = $client->request('GET', '/conversation/' . $message->getId());

        $this->assertResponseIsSuccessful();
        // The photo as a picture of its own, without an empty text bubble
        $photo = $crawler->filter('.c-bubble-photos__img[src="/gallery/show/uploaded/1/zztestphoto"]');
        self::assertCount(1, $photo);
        self::assertCount(0, $photo->closest('.c-bubble-stack')->filter('.c-bubble'));

        $input = $crawler->filter('form.js-inbox-reply input[type="file"].js-photo-input');
        self::assertCount(1, $input);
        self::assertSame('/gallery/upload/image', $input->attr('data-upload-url'));
        self::assertCount(1, $crawler->filter('form.js-inbox-reply .c-photo-attach.js-photo-attach'));
        self::assertCount(1, $crawler->filter('form.js-inbox-reply .js-photo-tray .js-photo-strip'));

        $item = $crawler->filter('.c-inbox-item__link[href*="/conversation/' . $message->getId() . '"]');
        self::assertStringContainsString('message.photo.preview', $item->filter('.c-inbox-item__preview')->text());
        // Received: the orange arrow; the sender sees the grey one
        self::assertCount(1, $item->filter('.c-inbox-item__dir--received'));
        self::assertCount(0, $item->filter('.c-inbox-item__dir--sent'));

        $client->loginUser($sender);
        $crawler = $client->request('GET', '/conversations/');
        $item = $crawler->filter('.c-inbox-item__link[href*="/conversation/' . $message->getId() . '"]');
        self::assertCount(1, $item->filter('.c-inbox-item__dir--sent'));
    }
}

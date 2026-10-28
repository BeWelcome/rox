<?php

namespace App\Tests\Service;

use App\Entity\Member;
use App\Entity\Newsletter;
use App\Service\Mailer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mime\Email;

/**
 * A newsletter carries the bulk-mail headers Gmail and Yahoo ask for (List-Unsubscribe with the member's own
 * unsubscribe link, one-click unsubscribe, Precedence: bulk); transactional mails (reminders, suspension, terms
 * of use) don't.
 */
#[Group('integration')]
class NewsletterHeadersTest extends KernelTestCase
{
    private const string KEY = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';

    public function testANewsletterCanBeUnsubscribedWithOneClick(): void
    {
        $headers = $this->send(Newsletter::REGULAR_NEWSLETTER)->getHeaders();

        self::assertSame(
            '<http://localhost/unsubscribe/newsletter/member-2/' . self::KEY . '>',
            $headers->get('List-Unsubscribe')?->getBodyAsString()
        );
        self::assertSame('List-Unsubscribe=One-Click', $headers->get('List-Unsubscribe-Post')?->getBodyAsString());
        self::assertSame('bulk', $headers->get('Precedence')?->getBodyAsString());
    }

    public static function transactionalTypes(): iterable
    {
        yield 'login reminder' => ['RemindToLog'];
        yield 'suspension' => [Newsletter::SUSPENSION_NOTIFICATION];
        yield 'terms of use' => [Newsletter::TERMS_OF_USE];
    }

    #[DataProvider('transactionalTypes')]
    public function testATransactionalMailHasNoUnsubscribeHeaders(string $type): void
    {
        $headers = $this->send($type)->getHeaders();

        self::assertFalse($headers->has('List-Unsubscribe'));
        self::assertFalse($headers->has('Precedence'));
    }

    private function send(string $type): Email
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $members = $entityManager->getRepository(Member::class);
        $newsletter = new Newsletter();
        $newsletter->setName('zztest_headers');
        $newsletter->setType($type);
        $newsletter->setCreatedBy($members->findOneBy(['username' => 'bwadmin']));
        $newsletter->setCreated(new DateTime());
        $entityManager->persist($newsletter);
        $entityManager->flush();

        static::getContainer()->get(Mailer::class)->sendNewsletterEmail(
            $newsletter,
            $members->findOneBy(['username' => 'member-2']),
            ['unsubscribe_key' => self::KEY]
        );

        self::assertEmailCount(1);
        $email = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $email);

        return $email;
    }
}

<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use App\Entity\MemberPreference;
use App\Entity\Preference;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The number of travellers of a new request: the field starts empty, the member enters it. Anything but a whole
 * number from 1 to 20 is a form error, not a server error (rox #526: a text reached the setter, which takes an int).
 * The test database has no translations: a message is its key.
 */
#[Group('integration')]
final class RequestTravellersTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $members = $entityManager->getRepository(Member::class);
        $host = $members->findOneBy(['username' => 'member-5']);
        // the fixtures' members have no picture and About Me: the host takes requests without them
        foreach ([Preference::ALLOW_CONTACT_WITHOUT_PICTURE, Preference::ALLOW_CONTACT_WITHOUT_ABOUT_ME] as $codename) {
            $preference = $entityManager->getRepository(Preference::class)->findOneBy(['codename' => $codename]);
            $memberPreference = $entityManager->getRepository(MemberPreference::class)
                ->findOneBy(['member' => $host, 'preference' => $preference])
                ?? new MemberPreference()->setMember($host)->setPreference($preference);
            $entityManager->persist($memberPreference->setValue('Yes'));
        }
        $entityManager->flush();
        $this->client->loginUser($members->findOneBy(['username' => 'member-2']));
    }

    public function testTheFieldStartsEmpty(): void
    {
        $crawler = $this->client->request('GET', '/new/request/member-5');

        self::assertResponseIsSuccessful();
        $field = $this->travellersField($crawler);
        self::assertSame('number', $field->attr('type'));
        self::assertSame('', (string) $field->attr('value'));
    }

    /**
     * @return iterable<string, array{string, string|null}>
     */
    public static function numbers(): iterable
    {
        yield 'nothing' => ['', 'request.error.numberoftravellers.empty'];
        yield 'a word' => ['abc', 'request.error.numberoftravellers.range'];
        yield 'a number with words' => ['2 people', 'request.error.numberoftravellers.range'];
        yield 'a fraction' => ['1.5', 'request.error.numberoftravellers.range'];
        yield 'zero' => ['0', 'request.error.numberoftravellers.range'];
        yield 'below zero' => ['-3', 'request.error.numberoftravellers.range'];
        yield 'too many' => ['21', 'request.error.numberoftravellers.range'];
        yield 'one' => ['1', null];
        yield 'twenty' => ['20', null];
    }

    #[DataProvider('numbers')]
    public function testTheNumberIsAWholeNumberFromOneToTwenty(string $travellers, ?string $message): void
    {
        $crawler = $this->client->request('GET', '/new/request/member-5');
        $form = $crawler->filter('form[name="app_message"]')->form();
        $form[(string) $this->travellersField($crawler)->attr('name')]->setValue($travellers);

        // the other fields stay empty: the form comes back, with or without a message about the travellers
        $this->client->submit($form);

        self::assertContains($this->client->getResponse()->getStatusCode(), [200, 422]);
        $content = (string) $this->client->getResponse()->getContent();
        foreach (['request.error.numberoftravellers.empty', 'request.error.numberoftravellers.range'] as $key) {
            if ($key === $message) {
                self::assertStringContainsString($key, $content);
            } else {
                self::assertStringNotContainsString($key, $content);
            }
        }
        self::assertStringNotContainsString('This value should be', $content);
    }

    private function travellersField(Crawler $crawler): Crawler
    {
        $field = $crawler->filter('form[name="app_message"] input[name$="[numberOfTravellers]"]');
        self::assertCount(1, $field);

        return $field;
    }
}

<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The FAQ and its admin: members see the active questions of a category and the categories that have some; the FAQ
 * volunteers (bwadmin has the Faq right) see every FAQ, sort them and the categories, and edit them. In the
 * fixtures the first category has three FAQs, the second two.
 */
#[Group('integration')]
final class FaqPagesTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testMembersSeeTheActiveQuestionsAsDetails(): void
    {
        [$first, $second, $third] = $this->faqs($this->firstCategory());
        $this->connection()->executeStatement("UPDATE faq SET Active = 'Not Active' WHERE id = ?", [$third]);

        $crawler = $this->client->request('GET', '/faq/' . $this->firstCategory());

        $this->assertResponseIsSuccessful();
        self::assertSame(['question' . $first, 'question' . $second], $crawler->filter('details.p-faq__item')->each(static fn ($item): string => (string) $item->attr('id')));
        self::assertCount(1, $crawler->filter('.p-faq__category.is-active'));
        self::assertSame('2', trim($crawler->filter('.p-faq__category.is-active .p-faq__count')->text()));
    }

    public function testTheAdminSeesEveryFaqAndSortsThem(): void
    {
        $category = $this->firstCategory();
        [$first, $second, $third] = $this->faqs($category);
        $this->connection()->executeStatement("UPDATE faq SET Active = 'Not Active' WHERE id = ?", [$third]);
        $this->client->loginUser($this->member('bwadmin'));

        $crawler = $this->client->request('GET', '/admin/faqs/' . $category);

        $this->assertResponseIsSuccessful();
        self::assertCount(3, $crawler->filter('.p-faq__row'));
        self::assertCount(1, $crawler->filter('.p-faq__row.is-inactive[data-id="' . $third . '"]'));

        // an id of another category is left out
        $other = $this->faqs($this->secondCategory())[0];
        $form = $crawler->filter('form.js-faq-sort')->form(['form[sortOrder]' => $third . ',' . $other . ',' . $first . ',' . $second]);
        $this->client->submit($form);

        $this->assertResponseRedirects();
        self::assertSame([$third, $first, $second], $this->faqs($category));
        self::assertSame(0, (int) $this->connection()->fetchOne('SELECT SortOrder FROM faq WHERE id = ?', [$other]));
    }

    public function testTheCategoriesAreSorted(): void
    {
        $this->client->loginUser($this->member('bwadmin'));
        $crawler = $this->client->request('GET', '/admin/faqs/sort');
        $ids = $crawler->filter('.p-faq__row')->each(static fn ($row): int => (int) $row->attr('data-id'));

        $this->client->submit($crawler->filter('form.js-faq-sort')->form(['form[sortOrder]' => implode(',', array_reverse($ids))]));

        $this->assertResponseRedirects('/admin/faqs/sort');
        self::assertSame(array_reverse($ids), array_map('intval', $this->connection()->fetchFirstColumn('SELECT id FROM faqcategories ORDER BY sortOrder, id')));
    }

    public function testTheTranslationIdIsFixedWhenEditing(): void
    {
        $faq = $this->faqs($this->firstCategory())[0];
        $this->client->loginUser($this->member('bwadmin'));

        $crawler = $this->client->request('GET', '/admin/faqs/faq/' . $faq . '/edit');
        $this->assertResponseIsSuccessful();
        self::assertSame('disabled', $crawler->filter('input[name="faq_form[wordCode]"]')->attr('disabled'));
        self::assertCount(1, $crawler->filter('button[name="faq_form[FaqUpdate]"]'));

        $crawler = $this->client->request('GET', '/admin/faqs/category/create');
        self::assertCount(1, $crawler->filter('button[name="faq_category_form[FaqCategoryCreate]"]'));
        self::assertCount(0, $crawler->filter('button[name="faq_category_form[FaqCategoryUpdate]"]'));
    }

    public function testAnEditSavesTheEnglishTexts(): void
    {
        $faq = $this->faqs($this->firstCategory())[0];
        $code = (string) $this->connection()->fetchOne('SELECT QandA FROM faq WHERE id = ?', [$faq]);
        $this->client->loginUser($this->member('bwadmin'));

        $crawler = $this->client->request('GET', '/admin/faqs/faq/' . $faq . '/edit');
        $this->client->submit($crawler->selectButton('faq_form[FaqUpdate]')->form([
            'faq_form[question]' => '<p>zztest question?</p>',
            'faq_form[answer]' => '<p>zztest answer.</p>',
        ]));

        $this->assertResponseRedirects();
        self::assertSame('<p>zztest question?</p>', $this->connection()->fetchOne("SELECT Sentence FROM word WHERE code = ? AND ShortCode = 'en'", ['faqq_' . $code]));
        self::assertSame('<p>zztest answer.</p>', $this->connection()->fetchOne("SELECT Sentence FROM word WHERE code = ? AND ShortCode = 'en'", ['faqa_' . $code]));
    }

    public function testOthersDontSeeTheAdmin(): void
    {
        $this->client->loginUser($this->member('member-6'));

        foreach (['/admin/faqs/1', '/admin/faqs/sort', '/admin/faqs/category/create'] as $page) {
            $this->client->request('GET', $page);
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @return list<int> the FAQs of the category in their order
     */
    private function faqs(int $category): array
    {
        return array_map('intval', $this->connection()->fetchFirstColumn('SELECT id FROM faq WHERE IdCategory = ? ORDER BY SortOrder, id', [$category]));
    }

    private function firstCategory(): int
    {
        return (int) $this->connection()->fetchOne('SELECT IdCategory FROM faq GROUP BY IdCategory ORDER BY COUNT(*) DESC, IdCategory LIMIT 1');
    }

    private function secondCategory(): int
    {
        return (int) $this->connection()->fetchOne('SELECT IdCategory FROM faq WHERE IdCategory <> ? LIMIT 1', [$this->firstCategory()]);
    }

    private function member(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function connection(): Connection
    {
        return static::getContainer()->get(Connection::class);
    }
}

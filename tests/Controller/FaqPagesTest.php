<?php

namespace App\Tests\Controller;

use App\Doctrine\DomainType;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\Word;
use App\Model\TranslationModel;
use DateTime;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The FAQ and its admin: members see the active questions of a category in the About pages' frame; the FAQ
 * volunteers (bwadmin has the Faq right) see every FAQ, sort them and the categories, and edit them. In the
 * fixtures the first category has three FAQs, the second two.
 */
#[Group('integration')]
final class FaqPagesTest extends WebTestCase
{
    private KernelBrowser $client;

    /** @var array<string, ?string> English texts to put back into the cached catalogues (code => sentence, null: new) */
    private array $cachedTexts = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function tearDown(): void
    {
        // the database is rolled back, the cached catalogues aren't: they get the texts of before the test again
        foreach ($this->cachedTexts as $code => $sentence) {
            if (null === $sentence) {
                $this->connection()->executeStatement('DELETE FROM word WHERE code = ?', [$code]);
            } else {
                $this->connection()->executeStatement("UPDATE word SET Sentence = ? WHERE code = ? AND ShortCode = 'en'", [$sentence, $code]);
            }
            static::getContainer()->get(TranslationModel::class)->updateCachedText($code, 'en');
        }
        parent::tearDown();
    }

    public function testMembersSeeTheActiveQuestionsAsDetails(): void
    {
        [$first, $second, $third] = $this->faqs($this->firstCategory());
        $this->connection()->executeStatement("UPDATE faq SET Active = 'Not Active' WHERE id = ?", [$third]);

        $crawler = $this->client->request('GET', '/faq/' . $this->firstCategory());

        $this->assertResponseIsSuccessful();
        self::assertSame(['question' . $first, 'question' . $second], $crawler->filter('details.p-faq__item')->each(static fn ($item): string => (string) $item->attr('id')));
        // in the About pages' frame, the category marked in the About menu
        self::assertCount(1, $crawler->filter('.p-about__nav .p-about__link.is-active'));
        self::assertCount(1, $crawler->filter('.p-faq__help a[href="/about/feedback"]'));
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

    /**
     * The FAQ shows the edited texts at once, in English and, as their fallback, in languages without their own
     * translation (every language's cached catalogue has the English texts).
     */
    public function testAnEditShowsTheNewTextsAtOnce(): void
    {
        $category = $this->firstCategory();
        $faq = $this->faqs($category)[0];
        $code = (string) $this->connection()->fetchOne('SELECT QandA FROM faq WHERE id = ?', [$faq]);
        foreach (['faqq_', 'faqa_'] as $prefix) {
            $this->cachedTexts[$prefix . $code] = (string) $this->connection()->fetchOne("SELECT Sentence FROM word WHERE code = ? AND ShortCode = 'en'", [$prefix . $code]);
        }
        $fallback = $this->untranslatedLocale('faqq_' . $code, 'faqa_' . $code);
        $this->cacheCatalogues('/faq/' . $category, $fallback);

        $this->client->loginUser($this->member('bwadmin'));
        $crawler = $this->client->request('GET', '/admin/faqs/faq/' . $faq . '/edit');
        $this->client->submit($crawler->selectButton('faq_form[FaqUpdate]')->form([
            'faq_form[question]' => '<p>zztest new question?</p>',
            'faq_form[answer]' => '<p>zztest new answer.</p>',
        ]));
        $this->assertResponseRedirects();

        $crawler = $this->client->request('GET', '/faq/' . $category);
        self::assertSame('zztest new question?', trim($crawler->filter('#question' . $faq . ' summary')->text()));
        self::assertStringContainsString('zztest new answer.', $crawler->filter('#question' . $faq . ' .p-faq__answer')->text());
        $translator = static::getContainer()->get('translator');
        self::assertSame('<p>zztest new question?</p>', $translator->trans('faqq_' . $code, [], 'messages', $fallback), 'the fallback of ' . $fallback);
    }

    /**
     * A new question is on the FAQ at once, and its English texts are the fallback of the other languages.
     */
    public function testANewQuestionIsOnTheFaqAtOnce(): void
    {
        $category = $this->firstCategory();
        $this->cachedTexts = ['faqq_zztestnewquestion' => null, 'faqa_zztestnewquestion' => null];
        $fallback = $this->untranslatedLocale();
        $this->cacheCatalogues('/faq/' . $category, $fallback);

        $this->client->loginUser($this->member('bwadmin'));
        $crawler = $this->client->request('GET', '/admin/faqs/' . $category . '/create');
        $this->client->submit($crawler->selectButton('faq_form[FaqCreate]')->form([
            'faq_form[wordCode]' => 'zztestnewquestion',
            'faq_form[question]' => '<p>zztest brand new question?</p>',
            'faq_form[answer]' => '<p>zztest brand new answer.</p>',
            'faq_form[active]' => '1',
        ]));
        $this->assertResponseRedirects();

        $crawler = $this->client->request('GET', '/faq/' . $category);
        self::assertContains('zztest brand new question?', $crawler->filter('details.p-faq__item summary')->each(static fn ($summary): string => trim($summary->text())));
        $translator = static::getContainer()->get('translator');
        self::assertSame('<p>zztest brand new answer.</p>', $translator->trans('faqa_zztestnewquestion', [], 'messages', $fallback), 'the fallback of ' . $fallback);
    }

    /**
     * A category's new name is on the FAQ at once, also as the fallback of languages without their own translation.
     */
    public function testAnEditedCategoryNameIsShownAtOnce(): void
    {
        $category = $this->firstCategory();
        $code = strtolower((string) $this->connection()->fetchOne('SELECT Description FROM faqcategories WHERE id = ?', [$category]));
        $name = $this->connection()->fetchOne("SELECT Sentence FROM word WHERE code = ? AND ShortCode = 'en'", [$code]);
        if (false === $name) {
            // the fixtures have no names for the categories; a site has them
            $this->englishWord($code, 'zztest category');
            $name = null;
        }
        $this->cachedTexts[$code] = $name;
        $fallback = $this->untranslatedLocale($code);
        $this->cacheCatalogues('/faq/' . $category, $fallback);

        $this->client->loginUser($this->member('bwadmin'));
        $crawler = $this->client->request('GET', '/admin/faqs/category/' . $category . '/edit');
        $this->client->submit($crawler->selectButton('faq_category_form[FaqCategoryUpdate]')->form([
            'faq_category_form[description]' => 'zztest renamed category',
        ]));
        $this->assertResponseRedirects();

        $crawler = $this->client->request('GET', '/faq/' . $category);
        self::assertSame('zztest renamed category', trim($crawler->filter('h2.p-faq__title')->text()));
        $translator = static::getContainer()->get('translator');
        self::assertSame('zztest renamed category', $translator->trans($code, [], 'messages', $fallback), 'the fallback of ' . $fallback);
    }

    /**
     * A new category shows its name at once, not its code.
     */
    public function testANewCategoryIsNamedAtOnce(): void
    {
        // the form puts faq_cat_ in front of the code
        $this->cachedTexts = ['faq_cat_zztestnewcategory' => null];
        $fallback = $this->untranslatedLocale();
        $this->cacheCatalogues('/faq/' . $this->firstCategory(), $fallback);

        $this->client->loginUser($this->member('bwadmin'));
        $crawler = $this->client->request('GET', '/admin/faqs/category/create');
        $this->client->submit($crawler->selectButton('faq_category_form[FaqCategoryCreate]')->form([
            'faq_category_form[wordCode]' => 'zztestnewcategory',
            'faq_category_form[description]' => 'zztest new category',
        ]));
        $this->assertResponseRedirects();

        $crawler = $this->client->followRedirect();
        self::assertContains('zztest new category', $crawler->filter('a.p-faq__category span:first-child')->each(static fn ($name): string => trim($name->text())));
        $translator = static::getContainer()->get('translator');
        self::assertSame('zztest new category', $translator->trans('faq_cat_zztestnewcategory', [], 'messages', $fallback), 'the fallback of ' . $fallback);
    }

    public function testOthersDontSeeTheAdmin(): void
    {
        $this->client->loginUser($this->member('member-6'));

        foreach (['/admin/faqs/1', '/admin/faqs/sort', '/admin/faqs/category/create'] as $page) {
            $this->client->request('GET', $page);
            $this->assertResponseStatusCodeSame(403);
        }
    }

    private function englishWord(string $code, string $sentence): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $word = new Word()
            ->setCode($code)
            ->setSentence($sentence)
            ->setDomain(DomainType::MESSAGES)
            ->setLanguage($entityManager->getRepository(Language::class)->findOneBy(['shortCode' => 'en']))
            ->setCreated(new DateTime())
            ->setDescription('FAQ category')
            ->setAuthor($this->member('bwadmin'));
        $entityManager->persist($word);
        $entityManager->flush();
    }

    /**
     * A language in which none of these codes has its own translation: it shows the English texts.
     */
    private function untranslatedLocale(string ...$codes): string
    {
        $translated = [] === $codes ? [] : $this->connection()->fetchFirstColumn(
            'SELECT ShortCode FROM word WHERE code IN (?) AND ShortCode <> \'en\'',
            [$codes],
            [ArrayParameterType::STRING],
        );

        return array_values(array_diff(['de', 'fr', 'es'], $translated))[0];
    }

    /**
     * The catalogues of English and the language are cached, as on a running site.
     */
    private function cacheCatalogues(string $page, string $locale): void
    {
        $this->client->request('GET', $page);
        foreach (['en', $locale] as $cached) {
            static::getContainer()->get('translator')->getCatalogue($cached);
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

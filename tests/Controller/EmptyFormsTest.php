<?php

namespace App\Tests\Controller;

use App\Entity\FaqCategory;
use App\Entity\Member;
use App\Entity\MemberPreference;
use App\Entity\Preference;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * A form sent empty says what is missing, in words of its own. The browser stops most empty forms itself (required
 * fields), so these are the server's answers when it doesn't. Some were a server error (the empty value reached
 * code or the database that takes none), the others the validator's "This value should not be blank.".
 */
#[Group('integration')]
class EmptyFormsTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @return iterable<string, array{string, string, string, list<string>}> member, page, form name, the messages
     */
    public static function forms(): iterable
    {
        yield 'personal data: the password' => ['member-2', '/members/member-2/mydata', 'password_form', ['mydata.password.empty']];
        yield 'a hosting request' => ['member-2', '/new/request/member-5', 'app_message', ['request.error.duration']];
        yield 'the accommodation of the profile' => ['member-2', '/members/member-2/edit/en/accommodation', 'accommodation_form', ['profile.max.guests.empty']];
        yield 'find a member' => ['bwadmin', '/admin/tools/findmember', 'find_user_form', ['admin.tools.find_member.error.empty']];
        yield 'a new FAQ category' => ['bwadmin', '/admin/faqs/category/create', 'faq_category_form', ['admin.faq.category.code.empty', 'admin.faq.category.description.empty']];
        yield 'a new FAQ' => ['bwadmin', '/admin/faqs/{category}/create', 'faq_form', ['admin.faq.code.empty', 'admin.faq.question.empty', 'admin.faq.answer.empty']];
        yield 'a donation' => ['bwadmin', '/admin/treasurer/add', 'donation', ['admin.treasurer.amount.empty', 'admin.treasurer.date.empty']];
        yield 'a forgotten password' => ['', '/resetpassword', 'reset_password_request_form', ['resetpassword.username.empty']];
        yield 'the contact form' => ['member-2', '/feedback', '', ['feedback.message.empty']];
        yield 'a new password' => ['member-2', '/members/member-2/password/change', 'change_password_form', ['account.password.current.empty']];
        yield 'a new email address' => ['member-2', '/members/member-2/account/email', 'change_email_form', ['account.email.password.empty']];
        yield 'a note about a member' => ['member-2', '/members/member-3/note/add', 'profile_note', ['note.text.empty']];
        yield 'a new group' => ['member-2', '/new/group', 'group', ['group.name.empty', 'group.description.empty']];
        yield 'a new topic' => ['member-2', '/group/0/forum/new', 'forum_post', ['forum.post.title.empty', 'forum.post.text.empty']];
        yield 'community news' => ['bwadmin', '/admin/communitynews/create', 'community_news', ['communitynews.title.empty', 'communitynews.text.empty']];
        yield 'a group category' => ['bwadmin', '/admin/groups/categories', 'group_category_name', ['admin.group.category.name.empty']];
        yield 'renaming a group' => ['bwadmin', '/admin/groups/rename', 'form', ['admin.group.rename.group.empty', 'admin.group.rename.name.empty']];
        yield 'a site announcement' => ['bwadmin', '/admin/tools/login_message/add', 'login_message', ['admin.login_message.message.empty', 'admin.login_message.expires.empty']];
        yield 'forum notifications of a member' => ['bwadmin', '/admin/subscriptions', 'forum_notifications', ['admin.username.empty']];
        yield 'a new text to translate' => ['bwadmin', '/admin/translations/create', 'translation_form', ['translation.wordcode.empty', 'translation.description.empty', 'translation.englishtext.empty']];
    }

    /**
     * @param list<string> $messages
     */
    #[DataProvider('forms')]
    public function testAnEmptyFormSaysWhatIsMissing(string $username, string $page, string $formName, array $messages): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        if ('' !== $username) {  // a visitor's form otherwise
            $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
            self::assertInstanceOf(Member::class, $member);
            $this->client->loginUser($member);
        }
        if (str_contains($page, '{category}')) {
            $category = $entityManager->getRepository(FaqCategory::class)->findOneBy([]);
            self::assertInstanceOf(FaqCategory::class, $category);
            $page = str_replace('{category}', (string) $category->getId(), $page);
        }

        if (str_starts_with($page, '/new/request/')) {
            $this->acceptRequestsWithoutPictureAndAboutMe(basename($page));
        }

        $crawler = $this->client->request('GET', $page);
        $this->assertResponseIsSuccessful();
        // a form without a name is the page's own (its first with a text)
        $selector = '' !== $formName ? 'form[name="' . $formName . '"]' : 'main form:has(textarea)';
        $form = $crawler->filter($selector)->first()->form();
        // what a member types (and the dates the request form's calendar fills in); choices, switches and the
        // form's own hidden fields stay as they are
        $typed = 'textarea, input:not([type=hidden]):not([type=checkbox]):not([type=radio]):not([type=file]):not([type=submit])'
            . ', input[name$="[arrival]"], input[name$="[departure]"], input[name$="[numberOfTravellers]"]';
        $names = $crawler->filter($selector)->first()->filter($typed)->each(
            static fn ($field): string => (string) $field->attr('name'),
        );
        foreach (array_filter($names) as $name) {
            $form[$name]->setValue('');
        }
        $this->client->submit($form);

        $status = $this->client->getResponse()->getStatusCode();
        self::assertContains($status, [200, 422], 'the empty form answered ' . $status);
        $content = (string) $this->client->getResponse()->getContent();
        foreach ($messages as $message) {
            // the test database has no translations: the message is its key
            self::assertStringContainsString($message, $content);
        }
    }

    /**
     * The fixtures' members have no picture and About Me: the host takes requests without them.
     */
    private function acceptRequestsWithoutPictureAndAboutMe(string $host): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => $host]);
        foreach ([Preference::ALLOW_CONTACT_WITHOUT_PICTURE, Preference::ALLOW_CONTACT_WITHOUT_ABOUT_ME] as $codename) {
            $preference = $entityManager->getRepository(Preference::class)->findOneBy(['codename' => $codename]);
            $memberPreference = $entityManager->getRepository(MemberPreference::class)
                ->findOneBy(['member' => $member, 'preference' => $preference])
                ?? new MemberPreference()->setMember($member)->setPreference($preference);
            $entityManager->persist($memberPreference->setValue('Yes'));
        }
        $entityManager->flush();
    }
}

<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use App\Entity\MemberPhoto;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use ZipArchive;

/**
 * The export of a member's personal data, which the member asks for with their password on the mydata page: static
 * pages that work offline, each with the sidebar of all sections, the profile picture, and the conversations.
 */
#[Group('integration')]
class PersonalDataExportTest extends WebTestCase
{
    public function testTheExportContainsTheProfilePicture(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
        $member->setPassword(static::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($member, 'zztest password'));
        $picture = tempnam(sys_get_temp_dir(), 'zztest_avatar_');
        // a 1x1 PNG
        file_put_contents($picture, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=', true));
        $entityManager->persist(new MemberPhoto()->setMember($member)->setFilepath($picture));
        $entityManager->flush();

        try {
            $client->loginUser($member);
            // As in production: Twig without strict_variables, and the ICU text of the footer, which gets the date as
            // a parameter (the test database has no translations).
            $client->disableReboot();
            $client->getContainer()->get('twig')->disableStrictVariables();
            $client->getContainer()->get('translator')->getCatalogue('en')->set(
                'mydata.generated',
                'Data extracted on {date_generated, date, short} at {date_generated, time, short}.',
                'messages+intl-icu',
            );
            $crawler = $client->request('GET', '/members/member-2/mydata');
            $form = $crawler->filter('form')->form();
            $passwordField = $crawler->filter('form input[type=password]')->attr('name');
            $client->submit($form, [$passwordField => 'zztest password']);

            $this->assertResponseIsSuccessful();
            $zipFilename = $client->getRequest()->getSession()->get('mydata_file');
            $zip = new ZipArchive();
            self::assertTrue($zip->open($zipFilename));

            // the picture: in pictures/, shown by the profile and the overview; no page of its own
            $file = 'pictures/' . basename($picture) . '.png';
            self::assertNotFalse($zip->locateName($file));
            self::assertFalse($zip->locateName('pictures.html'));
            self::assertStringContainsString('href="' . $file . '"', (string) $zip->getFromName('profile.html'));
            self::assertStringContainsString('src="' . $file . '"', (string) $zip->getFromName('index.html'));

            $pages = [];
            for ($i = 0; $i < $zip->numFiles; ++$i) {
                $name = (string) $zip->getNameIndex($i);
                if (str_ends_with($name, '.html')) {
                    $pages[$name] = (string) $zip->getFromIndex($i);
                }
            }
            self::assertArrayHasKey('index.html', $pages);
            foreach ($pages as $name => $html) {
                // every page has the sidebar and the member, the markers are gone
                self::assertStringNotContainsString('<!--mydata:', $html, $name);
                self::assertStringContainsString('class="c-mydata-side"', $html, $name);
                self::assertStringContainsString('member-2', $html, $name);
                // a page in a subdirectory links up
                $prefix = str_contains($name, '/') ? '../' : '';
                self::assertStringContainsString('href="' . $prefix . 'index.html"', $html, $name);
                self::assertStringContainsString('href="' . $prefix . 'build/mydata', $html, $name);
            }

            // offline: the CSS's fonts, icons and images are in the zip, their url()s relative
            $cssName = 'build/mydata.css';
            $css = (string) $zip->getFromName($cssName);
            self::assertNotSame('', $css);
            self::assertStringNotContainsString('url(/build', $css);
            preg_match_all('~url\(["\']?(fonts/[^"\')?#]+)~', $css, $urls);
            self::assertNotEmpty($urls[1]);
            foreach (array_unique($urls[1]) as $url) {
                self::assertNotFalse($zip->locateName('build/' . $url), $url);
            }
            self::assertNotFalse($zip->locateName('images/logo_icon_2025.svg'));

            // the conversations: each row of a list opens a page that is in the zip
            foreach (['messages', 'requests', 'invitations'] as $section) {
                preg_match_all('~class="c-inbox-item__link" href="([^"]+)"~', $pages[$section . '.html'], $links);
                foreach ($links[1] as $link) {
                    self::assertArrayHasKey($link, $pages, $section);
                    self::assertStringContainsString('c-bubble', $pages[$link], $link);
                }
            }
            $zip->close();
            unlink($zipFilename);
        } finally {
            unlink($picture);
        }
    }
}

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
 * The export of a member's personal data, which the member asks for with their password on the mydata page,
 * includes the profile picture.
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
            self::assertNotFalse($zip->locateName('pictures/' . basename($picture) . '.png'));
            self::assertStringContainsString(basename($picture) . '.png', (string) $zip->getFromName('pictures.html'));
            $zip->close();
            unlink($zipFilename);
        } finally {
            unlink($picture);
        }
    }
}

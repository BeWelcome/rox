<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Every list of the translation tool opens for a translator (bwadmin has the Words right in the fixtures).
 */
#[Group('integration')]
class TranslationListControllerTest extends WebTestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function lists(): iterable
    {
        foreach (['all', 'missing', 'update', 'archived', 'donottranslate'] as $type) {
            yield $type => ['/admin/translations/' . $type . '/fr'];
        }
        yield 'all, searched' => ['/admin/translations/all/fr/welcome'];
    }

    #[DataProvider('lists')]
    public function testTheListOpens(string $url): void
    {
        $client = static::createClient();
        $translator = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']);
        $client->loginUser($translator);

        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }
}

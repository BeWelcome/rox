<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * A new email address waiting for its confirmation shows as a bell in the top bar, not as a banner
 * above the page; without one there is no bell.
 */
#[Group('integration')]
class TopbarNoticesTest extends WebTestCase
{
    public function testANewEmailToConfirmShowsTheBell(): void
    {
        $client = static::createClient();
        $member = $this->member('new-' . uniqid() . '@example.test');
        $client->loginUser($member);

        $client->request('GET', '/conversations/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#main_menu #notices_menu_toggle');
        $this->assertSelectorTextContains('#notices_menu', $member->getNewEmail());
        $this->assertSelectorExists('#notices_menu a[href*="/change/email"]');
        $this->assertSelectorNotExists('main .alert-notice');
    }

    public function testNothingToDoShowsNoBell(): void
    {
        $client = static::createClient();
        $client->loginUser($this->member(null));

        $client->request('GET', '/conversations/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('#notices_menu_toggle');
    }

    private function member(?string $newEmail): Member
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
        $member->setNewEmail($newEmail);
        $entityManager->flush();

        return $member;
    }
}

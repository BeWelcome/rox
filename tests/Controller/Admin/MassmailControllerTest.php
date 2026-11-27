<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\MassmailController;
use App\Entity\Member;
use App\Entity\Newsletter;
use App\Exception\UntranslatedNewsletterException;
use App\Repository\MemberRepository;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class MassmailControllerTest extends TestCase
{
    private function member(string $username): Member
    {
        $member = $this->createStub(Member::class);
        $member->method('getUsername')->willReturn($username);

        return $member;
    }

    private function controller(array &$flashes): MassmailController
    {
        $form = $this->createStub(FormInterface::class);
        $form->method('handleRequest')->willReturnSelf();
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn(['members' => 'anna, ben']);

        $builder = $this->createStub(FormBuilderInterface::class);
        $builder->method('add')->willReturnSelf();
        $builder->method('getForm')->willReturn($form);

        $controller = $this->getMockBuilder(MassmailController::class)
            ->onlyMethods(['isGranted', 'createFormBuilder', 'addFlash', 'redirectToRoute'])
            ->getMock();
        $controller->method('isGranted')->willReturn(true);
        $controller->method('createFormBuilder')->willReturn($builder);
        $controller->method('addFlash')->willReturnCallback(
            function (string $type, $message) use (&$flashes): void {
                $flashes[] = [$type, $message];
            }
        );
        $controller->method('redirectToRoute')->willReturn(new RedirectResponse('/admin/massmail'));

        return $controller;
    }

    private function entityManager(array $members): EntityManagerInterface
    {
        $repository = $this->createStub(MemberRepository::class);
        $repository->method('findBy')->willReturn($members);
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        return $entityManager;
    }

    public function testAnUntranslatedNewsletterIsReportedAsAFlashAndTheLoopGoesOn(): void
    {
        $flashes = [];
        $anna = $this->member('anna');
        $ben = $this->member('ben');

        $sent = [];
        $mailer = $this->createStub(Mailer::class);
        $mailer->method('sendNewsletterEmail')->willReturnCallback(
            function (Newsletter $newsletter, Member $receiver) use ($anna, &$sent): bool {
                if ($receiver === $anna) {
                    throw new UntranslatedNewsletterException('No translation of "broadcast_title_x", the mail is not sent.');
                }
                $sent[] = $receiver->getUsername();

                return true;
            }
        );

        $response = $this->controller($flashes)->testMassmailSending(
            new Request(),
            new Newsletter(),
            $this->entityManager([$anna, $ben]),
            $mailer
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(['ben'], $sent);
        $this->assertSame(
            [['error', 'anna: No translation of "broadcast_title_x", the mail is not sent.']],
            $flashes
        );
    }

    public function testAnotherFailureIsNotHidden(): void
    {
        $flashes = [];
        $mailer = $this->createStub(Mailer::class);
        $mailer->method('sendNewsletterEmail')->willThrowException(new \RuntimeException('SMTP is down'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('SMTP is down');

        $this->controller($flashes)->testMassmailSending(
            new Request(),
            new Newsletter(),
            $this->entityManager([$this->member('anna')]),
            $mailer
        );
    }
}

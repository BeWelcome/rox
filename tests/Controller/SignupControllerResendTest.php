<?php

namespace App\Tests\Controller;

use App\Controller\SignupController;
use App\Entity\Member;
use App\Service\Mailer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The confirmation mail is resent at most three times an hour per member; then the member goes back to the profile
 * with a notice.
 */
class SignupControllerResendTest extends TestCase
{
    /** @var SignupController&MockObject */
    private $controller;

    private Member $member;

    private AuthenticationUtils $helper;

    protected function setUp(): void
    {
        $this->member = $this->createMock(Member::class);
        $this->member->method('getId')->willReturn(2);
        $this->member->method('getUsername')->willReturn('member-2');

        $this->controller = $this->createPartialMock(
            SignupController::class,
            ['render', 'addFlash', 'redirectToRoute']
        );
        $this->controller->method('render')->willReturn(new Response('resent'));
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);
        $this->controller->setTranslator($translator);

        $this->helper = $this->createMock(AuthenticationUtils::class);
        $this->helper->method('getLastUsername')->willReturn('member-2');
    }

    public function testTheConfirmationMailIsResentThreeTimesAnHour(): void
    {
        $limiter = new RateLimiterFactory(
            ['id' => 'confirmation_mail', 'policy' => 'sliding_window', 'limit' => 3, 'interval' => '1 hour'],
            new InMemoryStorage()
        );
        $mailer = $this->createMock(Mailer::class);
        $mailer->expects(self::exactly(3))->method('sendSignupEmail');
        $this->controller->expects(self::once())->method('addFlash')->with('error', 'flash.signup.resend.too.many');
        $this->controller->expects(self::once())->method('redirectToRoute')
            ->with('members_profile', ['username' => 'member-2'])
            ->willReturn(new RedirectResponse('/members/member-2'));

        for ($i = 1; $i <= 3; ++$i) {
            $response = $this->controller->resendConfirmationEmail($this->member, $this->helper, $mailer, $limiter);
            self::assertSame('resent', $response->getContent(), 'mail ' . $i . ' is sent');
        }

        $response = $this->controller->resendConfirmationEmail($this->member, $this->helper, $mailer, $limiter);

        self::assertInstanceOf(RedirectResponse::class, $response);
    }
}
